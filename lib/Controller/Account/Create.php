<?php
/**
 * Create Account
 */

namespace App\Controller\Account;

class Create extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page'] = [ 'title' => 'Create Account' ];

		if (!empty($_GET['r'])) {
			$_SESSION['return-path'] = $_GET['r'];
		}

		if (empty($_SESSION['account-create'])) {
			$_SESSION['account-create'] = [];
		}

		if (!empty($_GET['service'])) {
			$_SESSION['account-create']['service'] = $_GET['service'];
		}

		switch ($_GET['e']) {
		case 'cac035':
			// Invalid Email Address
			break;
		}

		$cfg = \OpenTHC\Config::get('google');
		$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

		$data['CSRF'] = \App\CSRF::getToken();

		return $RES->write( $this->render('account/create.php', $data) );
	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		// _check_recaptcha();
		$chk = \App\CSRF::verify($_POST['CSRF']);
		if (empty($chk)) {
			return $RES->withRedirect('/account/create?e=cac049');
		}

		switch ($_POST['a']) {
		case 'contact-next':
			return $this->_create_account($RES);
		}

		$RES->withJSON([
			'data' => null,
			'meta' => [ 'Invalid Request [CAC-055]' ],
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

		// Contact Table
		$Contact = [
			'id' => _ulid(),
			'name' => $_POST['contact-name'],
			'email' => $_POST['contact-email'],
			'hash' => '-',
		];
		$dbc_main->insert('contact', $Contact);
		$dbc_auth->insert('auth_contact', array(
			'id' => $Contact['id'],
			'username' => $Contact['email'],
			'password' => 'NONE:' . sha1(json_encode($_SERVER).json_encode($_POST)),
		));

		// Auth Hash Link
		$act = new \App\Auth_Context_Ticket($dbc_auth);
		$act->create(array(
			'intent' => 'account-create',
			'service' => $_SESSION['account-create']['service'],
			'contact' => [
				'id' => $Contact['id'],
				'name' => $Contact['name'],
				'email' => $Contact['email'],
			],
			'ip' => $_SERVER['REMOTE_ADDR'],
		));

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
				'_' => $act['id'],
			]));

		} else {

			$arg = [];
			$arg['address_target'] = $Contact['email'];
			$arg['file'] = 'sso/account-create.tpl';
			$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
			$arg['data']['mail_subject'] = 'Account Confirmation';
			$arg['data']['auth_context_ticket'] = $act['id'];

			try {

				$cic = new \OpenTHC\Service\OpenTHC('cic');
				$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);
				switch ($res['code']) {
					case 200:
					case 201:
						// Cool
						break;
					default:
						$ret_args['e'] = 'cac217';
						$ret_args['s'] = 'e';
						break;
				}

			} catch (\Exception $e) {
				// Ignore
				$ret_args['e'] = 'cac190';
				$ret_args['s'] = 'e';
			}

		}

		$RES = $RES->withAttribute('Contact', [
			'id' => $Contact['id'],
			'username' => $Contact['email'],
			'email' => $Contact['email'],
		]);

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}
}
