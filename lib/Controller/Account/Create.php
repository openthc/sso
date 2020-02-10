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

		if (empty($_SESSION['account-create']['region'])) {
			$file = 'page/account/create-0.html';
			return $this->_container->view->render($RES, $file, $data);
		}

		$data['region'] = $_SESSION['account-create']['region'];

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
		$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
		if (empty($email)) {
			return $RES->withRedirect('/account/create?e=cac035');
		}

		// Lookup Company
		// $dir = new \Service_OpenTHC('dir');
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
		$sql = 'SELECT * FROM auth_contact WHERE username = ?';
		$arg = array($email);
		$res = $dbc->fetchRow($sql, $arg);
		if (!empty($res)) {
			return $RES->withRedirect('/auth/done?e=cac065');
		}

		if (!empty($_POST['company-id'])) {
			$chk = $dbc->fetchRow('SELECT id FROM company WHERE id = ?', [$_POST['company-id']]);
			if (empty($chk['id'])) {
				$_SESSION['account-create']['company-create'] = true;
				// return $RES->withRedirect('/auth/done?e=cac093');
			}
		}

		if (!empty($_POST['license-id'])) {
			$chk = $dbc->fetchRow('SELECT id FROM license WHERE id = ?', [$_POST['license-id']]);
			if (empty($chk['id'])) {
				$_SESSION['account-create']['license-create'] = true;
				// return $RES->withRedirect('/auth/done?e=cac093');
			}
		}

		$company_id = $dbc->insert('company', [
			'id' => \Edoceo\Radix\ULID::generate(),
			'stat' => 100,
			'type' => 'X',
			'name' => $_POST['license-name'],
		]);

		$dbc->insert('auth_company', [
			'id' => $company_id,
		]);

		// Contact Table
		$contact_id = $dbc->insert('contact', [
			'id' => \Edoceo\Radix\ULID::generate(),
			'fullname' => $_POST['contact-name'],
			'email' => $email,
			'phone' => $phone,
		]);

		$contact_id = $dbc->insert('auth_contact', array(
			'id' => $contact_id,
			'company_id' => $company_id,
			'username' => $email,
			'password' => 'NONE:' . sha1(json_encode($_SERVER).json_encode($_POST)),
		));

		// Auth Hash Link
		$ah = [];
		$ah['json'] = json_encode(array(
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
					'email' => $email,
					'phone' => $_POST['phone'],
				]
			],
			'geoip' => geoip_record_by_name($_SERVER['REMOTE_ADDR']),
		));
		$ah['hash'] = sha1(microtime(true) . serialize($ah) . serialize($_SESSION));
		$ah['id'] = $dbc->insert('auth_hash', $ah);

		$arg = [];
		$arg['to'] = $email;
		$arg['file'] = 'sso/account-create.tpl';
		$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
		$arg['data']['mail_subj'] = 'Account Confirmation';
		$arg['data']['account_create_hash'] = $ah['hash'];
		$arg['data']['sign_up_hash'] = $ah['hash']; // @deprecated

		$cic = new \Service_OpenTHC('cic');
		$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);

		return $RES->withRedirect('/auth/done?e=cac111');

	}
}
