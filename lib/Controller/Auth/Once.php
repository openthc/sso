<?php
/**
 * One Time Link Handler
 */

namespace App\Controller\Auth;

use Edoceo\Radix\Session;

use App\Contact;

class Once extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		session_start();

		if (empty($_GET['a'])) {
			_exit_html('<h1>Invalid Request [CAO#020]</h1>', 400);
		}

		// Well known actions
		switch ($_GET['a']) {
		case 'password-reset':

			$cfg = \OpenTHC\Config::get('google');

			$file = 'page/auth/once-password-reset.html';
			$data = [];
			$data['Page'] = [ 'title' => 'Password Reset '];
			$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

			return $this->_container->view->render($RES, $file, $data);

			break;
		}

		if (!preg_match('/^([0-9a-f]{32,128})$/', $_GET['a'], $m)) {
			_exit_html('<h1>Invalid Request [CAO#024]</h1>', 400);
		}

		$dbc = $this->_container->DB;

		$chk = $dbc->fetchRow('SELECT * FROM auth_hash WHERE hash = ?', $_GET['a']);
		if (empty($chk)) {
			_exit_html('<h1>Invalid Token [CAO#023]</h1><p>The link you followed is not valid</p><p>You may need to ', 400);
		}

		if (strtotime($chk['ts_expires']) < $_SERVER['REQUEST_TIME']) {
			$dbc->query('DELETE FROM auth_hash WHERE id = ?', $chk['id']);
			_exit_html('<h1>Invalid Token [CAO#028]</h2><p>The link you followed has expired</p>', 400);
		}

		$data = json_decode($chk['json'], true);
		if (empty($data)) {
			$dbc->query('DELETE FROM auth_hash WHERE id = ?', $chk['id']);
			_exit_html('<h1>Invalid Token [CAO#040]</h2><p>The token provided was not valid.</p>', 400);
		}

		switch ($data['action']) {
		case 'account-create':
			return $this->accountCreate($RES, $data);
			break;
		case 'password-reset':
			$_SESSION['key'] = sha1(openssl_random_pseudo_bytes(256));
			$val = [
				'contact' => $data['contact']
			];
			$val = json_encode($val);
			$x = _encrypt($val, $_SESSION['key']);
			return $RES->withRedirect('/account/password?_=' . $x);
		}

		_exit_text($data);

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
			_exit_text('Invalid [CAO#073]');
		}

		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $chk,
			':f1' => \App\Contact::FLAG_EMAIL_GOOD | \App\Contact::FLAG_PHONE_WANT,
		];
		$dbc->query($sql, $arg);

		// $_SESSION['uid'] = $data['account']['contact']['id'];
		// $_SESSION['gid'] = $data['account']['company']['id'];
		$_SESSION['email'] = $data['account']['contact']['email'];

		return $RES->withRedirect('/auth/done?e=cao073');

	}

	private function sendPasswordReset($RES)
	{
		_check_recaptcha();

		$username = strtolower(trim($_POST['username']));
		$username = \Edoceo\Radix\Filter::email($username);
		if (empty($username)) {
			// Render Fail?
			return $RES->withRedirect('/auth/once?a=password-reset&e=cao075');
		}

		$dbc = $this->_container->DB;
		$Contact = $dbc->fetchRow('SELECT id, username FROM auth_contact WHERE username = ?', [ $username ]);
		if (empty($Contact)) {
			Session::flash('info', 'Please Create an Account to use OpenTHC');
			return $RES->withRedirect('/auth/create?e=cao063');
		}

		// if ($AU->hasFlag(Contact::FLAG_DISABLED)) {
		// 	Session::flash('fail', 'There is some issue with your account, please contact support [CAR#032]');
		// 	Radix::redirect();
		// }

		// Generate Authentication Hash
		$ah = [];
		$ah['json'] = json_encode(array(
			'action' => 'password-reset',
			'contact' => $Contact,
			'geoip' => geoip_record_by_name($_SERVER['REMOTE_ADDR']),
		));
		$ah['hash'] = sha1(microtime(true) . serialize($ah) . serialize($_SESSION));
		$ah['id'] = $dbc->insert('auth_hash', $ah);
		// var_dump($ah);

		// Use CIC to Send
		$cic = new \Service_OpenTHC('cic');
		$arg['to'] = $Contact['username'];
		$arg['file'] = 'sso/password-reset.tpl';
		$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
		$arg['data']['mail_subj'] = 'Password Reset Request';
		$arg['data']['auth_hash'] = $ah['hash'];

		$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);
		// var_dump($res);

		return $RES->withRedirect('/auth/done?e=cao100');

	}
}
