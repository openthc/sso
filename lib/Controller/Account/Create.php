<?php
/**
 * Create Account
 */

namespace App\Controller\Account;

class Create extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$file = 'page/account/create.html';
		$data = [];
		$data['Page'] = [ 'title' => 'Create Account' ];

		if (!empty($_GET['r'])) {
			$_SESSION['return-path'] = $_GET['r'];
		}

		if (empty($_SESSION['account-create'])) {
			$_SESSION['account-create'] = [];
		}

		if (!empty($_GET['origin'])) {
			$_SESSION['account-create']['origin'] = $_GET['origin'];
		}

		switch ($_GET['e']) {
		case 'cac035':
			// Invalid Email Address
			break;
		}

		$cfg = \OpenTHC\Config::get('google');
		$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

		return $this->_container->view->render($RES, $file, $data);
	}

	function post($REQ, $RES, $ARG)
	{
		// _check_recaptcha();

		switch ($_POST['a']) {
		case 'region-next':
			$_SESSION['account-create']['region'] = $_POST['region'];
			return $RES->withRedirect('/account/create');
		case 'contact-next':
			return $this->_create_account($RES);
		}

		_exit_html('Invalid Request [CAC#055]', 400);

	}

	private function _create_account($RES)
	{
		$_POST['email'] = strtolower(trim($_POST['email']));
		$_POST['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
		if (empty($_POST['email'])) {
			return $RES->withRedirect('/account/create?e=cac035');
		}

		$_POST['phone'] = _phone_e164($_POST['phone']);

		// Lookup Company
		// $dir = new \App\Service\OpenTHC('dir');
		// $chk = $dir->get('company?q=' . $_POST['company-name']);
		// switch ($chk['code']) {
		// case '404':
		// 	$_SESSION['company-create'] = true;
		// 	break;
		// }
		// var_dump($chk);

		// $chk = $dir->get('contact?q=' . $_POST['email']);
		// switch ($chk['code']) {
		// case '404':
		// 	$_SESSION['contact-create'] = true;
		// 	break;
		// }
		// var_dump($dir);

		$dbc = $this->_container->DB;

		// Contact
		$sql = 'SELECT id, username FROM auth_contact WHERE username = ?';
		$arg = array($_POST['email']);
		$res = $dbc->fetchRow($sql, $arg);
		if (!empty($res)) {
			return $RES->withRedirect('/done?e=cac065');
		}

		if (!empty($_POST['company-id'])) {
			$chk = $dbc->fetchRow('SELECT id FROM company WHERE id = ?', [$_POST['company-id']]);
			if (empty($chk['id'])) {
				$_SESSION['account-create']['company-create'] = true;
				// return $RES->withRedirect('/done?e=cac093');
			}
		}

		if (!empty($_POST['license-id'])) {
			$chk = $dbc->fetchRow('SELECT id FROM license WHERE id = ?', [$_POST['license-id']]);
			if (empty($chk['id'])) {
				$_SESSION['account-create']['license-create'] = true;
				// return $RES->withRedirect('/done?e=cac093');
			}
		}

		$company_id = $dbc->insert('company', [
			'id' => \Edoceo\Radix\ULID::create(),
			'cre' => $_SESSION['account-create']['region'],
			'name' => $_POST['license-name'],
			'stat' => 100,
			'type' => 'X',
			'hash' => '-',
		]);

		$dbc->insert('auth_company', [
			'id' => $company_id,
			'code' => '-',
		]);

		// Contact Table
		$contact_id = $dbc->insert('contact', [
			'id' => \Edoceo\Radix\ULID::create(),
			'name' => $_POST['contact-name'],
			'email' => $_POST['email'],
			'phone' => $_POST['phone'],
			'hash' => '-',
		]);

		$contact_id = $dbc->insert('auth_contact', array(
			'id' => $contact_id,
			'company_id' => $company_id,
			'username' => $_POST['email'],
			'password' => 'NONE:' . sha1(json_encode($_SERVER).json_encode($_POST)),
		));

		// Auth Hash Link
		$acs = [];
		$acs['id'] = base64_encode_url(hash('sha256', openssl_random_pseudo_bytes(256), true));
		$acs['meta'] = json_encode(array(
			'action' => 'account-create',
			'account' => [
				'company' => [
					'id' => $company_id,
					'name' => $_POST['license-name'],
				],
				'license' => [
					'id' => $_POST['license-id'],
					'name' => $_POST['license-name'],
				],
				'contact' => [
					'id' => $contact_id,
					'name' => $_POST['contact-name'],
					'email' => $_POST['email'],
					'phone' => $_POST['phone'],
				]
			],
			'origin' => $_SESSION['account-create']['origin'],
			'geoip' => geoip_record_by_name($_SERVER['REMOTE_ADDR']),
		));
		$dbc->insert('auth_context_token', $acs);

		$arg = [];
		$arg['to'] = $_POST['email'];
		$arg['file'] = 'sso/account-create.tpl';
		$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
		$arg['data']['mail_subj'] = 'Account Confirmation';
		$arg['data']['once_hash'] = $acs['code'];
		$arg['data']['sign_up_hash'] = $acs['code']; // @deprecated

		try {
			$cic = new \OpenTHC\Service\OpenTHC('cic');
			$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);
		} catch (\Exception $e) {
			// Ignore
		}

		$RES = $RES->withAttribute('Contact', [
			'id' => $contact_id,
			'username' => $_POST['email'],
			'company_name' => $_POST['license-name'],
		]);

		return $RES->withRedirect('/done?e=cac111');

	}
}
