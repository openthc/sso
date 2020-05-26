<?php
/**
 * One Time Link Handler
 */

namespace App\Controller\Auth;

use App\Contact;

class Once extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Hash Links
		// Should be using a different controller?
		if (!empty($_GET['_'])) {

			$code = $_GET['_'];
			if (!preg_match('/^([\w\-]{32,128})$/i', $code, $m)) {
				_exit_json([
					'data' => null,
					'meta' => [ 'detail' => 'Invalid Request [CAO#024]' ]
				], 400);
			}

			$chk = $this->_container->Redis->get($code);
			if (empty($chk)) {
				_exit_json([
					'data' => null,
					'meta' => [ 'detail' => 'Invalid Request [CAO#032]' ]
				], 400);
			}

			_exit_json($chk);

		}

		if (empty($_GET['a'])) {
			_exit_text('Invalid Request [CAO#020]', 400);
		}


		$data = $this->data;

		// Well known actions
		switch ($_GET['a']) {
		case 'password-reset':

			$data['Page'] = [ 'title' => 'Password Reset '];
			$data['email'] = $_SESSION['email'];

			$cfg = \OpenTHC\Config::get('google');
			$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

			$file = 'page/auth/once-password-reset.html';

			return $this->_container->view->render($RES, $file, $data);

			break;
		}

		if (!preg_match('/^([\w\-]{32,128})$/i', $_GET['a'], $m)) {
			_exit_html('<h1>Invalid Request [CAO#024]</h1>', 400);
		}

		$auth = $_GET['a'];

		$dbc = $this->_container->DB;

		$act = $dbc->fetchRow('SELECT * FROM auth_context_token WHERE id = ?', $auth);
		if (empty($act)) {
			return $RES->withRedirect('/done?e=cao066');
		}
		// if (strtotime($act['ts_expires']) < $_SERVER['REQUEST_TIME']) {
			// $dbc->query('DELETE FROM auth_context_token WHERE id = ?', $act['id']);
			// _exit_html('<h1>Invalid Token [CAO#028]</h2><p>The link you followed has expired</p>', 400);
		// }
		$chk = json_decode($act['meta'], true);
		if (empty($chk)) {
			$dbc->query('DELETE FROM auth_context_token WHERE id = ?', $act['id']);
			return $RES->withRedirect('/done?e=cao077');
		}
		$act = $chk;

		switch ($act['action']) {
		case 'account-create':

			return $this->accountCreate($RES, $act);

		case 'email-verify':

			$arg = [
				'action' => 'email-verify-save',
				'contact' => $act['contact'],
			];
			$arg = json_encode($arg);
			$arg = _encrypt($arg, $_SESSION['crypt-key']);

			return $RES->withRedirect('/account/verify?_=' . $arg);

		case 'password-reset':

			$val = [
				'source' => 'email',
				'contact' => $act['contact'],
			];
			$val = json_encode($val);
			$x = _encrypt($val, $_SESSION['crypt-key']);

			return $RES->withRedirect('/account/password?_=' . $x);
		}

		$data['Page']['title'] = 'Error';
		$RES = $this->_container->view->render($RES, 'page/done.html', $data);
		return $RES->withStatus(400);

	}

	/**
	 * POST Handler
	 */
	function post($REQ, $RES, $ARG)
	{
		switch ($_POST['a']) {
		case 'password-reset-request':
			return $this->sendPasswordReset($RES);
		}
	}

	/**
	 *
	 */
	private function accountCreate($RES, $data)
	{
		$dbc = $this->_container->DB;

		// Update Contact
		$email = $data['account']['contact']['email'];
		$chk = $dbc->fetchOne('SELECT id FROM auth_contact WHERE username = ?', [ $email ]);
		if (empty($chk)) {
			_exit_text('Invalid [CAO#073]', 400);
		}

		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $chk,
			':f1' => \App\Contact::FLAG_EMAIL_GOOD | \App\Contact::FLAG_PHONE_WANT,
		];
		$dbc->query($sql, $arg);

		$_SESSION['email'] = $data['account']['contact']['email'];

		$val = [
			'contact' => [
				'id' => $data['account']['contact']['id'],
				'username' => $data['account']['contact']['email'],
			]
		];
		$val = json_encode($val);
		$_SESSION['account-create']['password-args'] = _encrypt($val, $_SESSION['crypt-key']);

		return $RES->withRedirect('/done?e=cao073');

	}

	/**
	 * Do the Password Reset Thing
	 */
	private function sendPasswordReset($RES)
	{
		// _check_recaptcha();

		$username = strtolower(trim($_POST['username']));
		$username = \Edoceo\Radix\Filter::email($username);
		if (empty($username)) {
			// Render Fail?
			return $RES->withRedirect('/auth/once?a=password-reset&e=cao075');
		}

		$_SESSION['email'] = $username;

		$dbc = $this->_container->DB;
		$Contact = $dbc->fetchRow('SELECT id, username FROM auth_contact WHERE username = ?', [ $username ]);
		if (empty($Contact)) {
			return $RES->withRedirect('/done?e=cao100&l=173');
		}


		// Generate Authentication Hash
		$acs = [];
		$acs['id'] = base64_encode_url(hash('sha256', openssl_random_pseudo_bytes(256), true));
		$acs['meta'] = json_encode(array(
			'action' => 'password-reset',
			'contact' => $Contact,
			'geoip' => geoip_record_by_name($_SERVER['REMOTE_ADDR']),
		));
		$dbc->insert('auth_context_token', $acs);

		$ret_args = [
			'e' => 'cao100',
			'l' => '200',
		];
		$ret_path = '/done';

		if ($_ENV['test']) {

			// Pass Information Back
			// Test Runner has to parse the Location URL
			$ret_args['r'] = "https://{$_SERVER['SERVER_NAME']}/auth/once";
			$ret_args['a'] = $acs['id'];

		} else {

			$arg = [];
			$arg['to'] = $Contact['username'];
			$arg['file'] = 'sso/contact-password-reset.tpl';
			$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
			$arg['data']['mail_subj'] = 'Password Reset Request';
			$arg['data']['auth_context_token'] = $acs['id'];

			// Use CIC to Send
			try {

				$cic = new \OpenTHC\Service\OpenTHC('cic');
				$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);

				$ret_args['s'] = 't';

			} catch (\Exception $e) {
				// Ignore
				$ret_args['s'] = 'f';
			}

		}

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}
}
