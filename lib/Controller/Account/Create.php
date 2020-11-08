<?php
/**
 * Create Account
 */

namespace App\Controller\Account;

class Create extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$file = 'page/account/create.html';
		$data = $this->data;
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

		$RES->withJSON([
			'data' => null,
			'meta' => [ 'Invalid Request [CAC#055]' ],
		], 400);

	}

	/**
	 * Create Account Process
	 */
	private function _create_account($RES)
	{
		$e = strtolower(trim($_POST['contact-email']));
		$e = filter_var($e, FILTER_VALIDATE_EMAIL);
		if (empty($e)) {
			return $RES->withRedirect('/account/create?e=cac035');
		}
		$_POST['contact-email'] = $e;

		$_POST['contact-phone'] = _phone_e164($_POST['contact-phone']);

		// Lookup Company
		// $dir = new \App\Service\OpenTHC('dir');
		// $chk = $dir->get('company?q=' . $_POST['company-name']);
		// switch ($chk['code']) {
		// case '404':
		// 	$_SESSION['company-create'] = true;
		// 	break;
		// }
		// var_dump($chk);

		// $chk = $dir->get('contact?q=' . $_POST['contact-email']);
		// switch ($chk['code']) {
		// case '404':
		// 	$_SESSION['contact-create'] = true;
		// 	break;
		// }
		// var_dump($dir);

		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		$dbc_auth->query('BEGIN');
		$dbc_main->query('BEGIN');

		// Contact
		$sql = 'SELECT id, email FROM contact WHERE email = ?';
		$arg = array($_POST['contact-email']);
		$res = $dbc_main->fetchRow($sql, $arg);
		if (!empty($res)) {
			return $RES->withRedirect('/done?e=cac065');
		}

		// Company Check
		$Company = [];
		if (empty($_POST['company-id'])) {

			$Company['id'] = _ulid();
			$Company['cre'] = $_SESSION['account-create']['region'];
			$Company['name'] = $_POST['license-name'];
			$Company['stat'] = 100;
			$Company['type'] = 'X';
			$Company['hash'] = md5(json_encode($Company));

			$dbc_main->insert('company', $Company);
			$dbc_auth->insert('auth_company', [
				'id' => $Company['id'],
				'name' => $Company['name'],
			]);

		} else {

			$chk = $dbc_main->fetchRow('SELECT id FROM company WHERE id = ?', [$_POST['company-id']]);
			if (empty($chk['id'])) {
				$_SESSION['account-create']['company-create'] = true;
				// return $RES->withRedirect('/done?e=cac093');
			}
		}

		if (!empty($_POST['license-id'])) {
			$chk = $dbc_main->fetchRow('SELECT id FROM license WHERE id = ?', [$_POST['license-id']]);
			if (empty($chk['id'])) {
				$_SESSION['account-create']['license-create'] = true;
				// return $RES->withRedirect('/done?e=cac093');
			}
		}

		// Contact Table
		$contact_id = $dbc_main->insert('contact', [
			'id' => _ulid(),
			'name' => $_POST['contact-name'],
			'email' => $_POST['contact-email'],
			'phone' => $_POST['contact-phone'],
			'hash' => '-',
		]);

		$dbc_auth->insert('auth_contact', array(
			'id' => $contact_id,
			'username' => $_POST['contact-email'],
			'password' => 'NONE:' . sha1(json_encode($_SERVER).json_encode($_POST)),
		));

		// Linkage
		$dbc_auth->insert('auth_company_contact', [
			'company_id' => $Company['id'],
			'contact_id' => $contact_id,
		]);

		// Auth Hash Link
		$acs = [];
		$acs['id'] = _random_hash();
		$acs['meta'] = json_encode(array(
			'action' => 'account-create',
			'account' => [
				'company' => [
					'id' => $Company['id'],
					'name' => $Company['name'],
				],
				'license' => [
					'id' => $_POST['license-id'],
					'name' => $_POST['license-name'],
				],
				'contact' => [
					'id' => $contact_id,
					'name' => $_POST['contact-name'],
					'email' => $_POST['contact-email'],
					'phone' => $_POST['contact-phone'],
				]
			],
			'origin' => $_SESSION['account-create']['origin'],
			'geoip' => geoip_record_by_name($_SERVER['REMOTE_ADDR']),
		));

		$dbc_auth->insert('auth_context_ticket', $acs);

		$dbc_auth->query('COMMIT');
		$dbc_main->query('COMMIT');

		// Return/Redirect
		$ret_args = [
			'e' => 'cac111',
		];
		$ret_path = '/done';

		// Test Mode
		if ($_ENV['test']) {

			$ret_args['r'] = sprintf('/auth/once?%s', http_build_query([
				'a' => $acs['id'],
			]));

		} else {

			$arg = [];
			$arg['to'] = $_POST['contact-email'];
			$arg['file'] = 'sso/account-create.tpl';
			$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
			$arg['data']['mail_subj'] = 'Account Confirmation';
			$arg['data']['auth_context_ticket'] = $acs['id'];

			try {

				$cic = new \OpenTHC\Service\OpenTHC('cic');
				$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);

			} catch (\Exception $e) {
				// Ignore
				$ret_args['e'] = 'cac190';
				$ret_args['s'] = 'e';
			}

		}

		$RES = $RES->withAttribute('Contact', [
			'id' => $contact_id,
			'username' => $_POST['email'],
			'company_name' => $_POST['license-name'],
			'contact_phone' => $_POST['contact-phone'],
		]);

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}
}
