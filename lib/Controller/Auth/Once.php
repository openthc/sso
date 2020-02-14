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

		if (empty($_SESSION['crypt-key'])) {
			$_SESSION['crypt-key'] = sha1(openssl_random_pseudo_bytes(256));
		}

		// Well known actions
		switch ($_GET['a']) {
		case 'password-reset':

			$cfg = \OpenTHC\Config::get('google');

			$file = 'page/auth/once-password-reset.html';
			$data = [];
			$data['Page'] = [ 'title' => 'Password Reset '];
			$data['email'] = $_SESSION['email'];
			$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

			return $this->_container->view->render($RES, $file, $data);

			break;
		}

		if (!preg_match('/^([\w\-]{32,128})$/i', $_GET['a'], $m)) {
			_exit_html('<h1>Invalid Request [CAO#024]</h1>', 400);
		}

		// $file = sprintf('%s/var/%s.json', APP_ROOT, $_GET['a']);
		// if (is_file($file)) {
		// 	_exit_text('NEw THING');
		// }

		// If the Hash is in Redis, then pass it back
		$hash = $_GET['a'];

		$R = new \Redis();
		$R->connect('127.0.0.1');
		$chk = $R->get($hash);
		if (!empty($chk)) {
			_exit_json($chk);
		}

		$dbc = $this->_container->DB;

		$chk = $dbc->fetchRow('SELECT * FROM auth_context_secret WHERE code = ?', $_GET['a']);
		if (empty($chk)) {
			return $RES->withRedirect('/done?e=cao066');
		}

		// if (strtotime($chk['ts_expires']) < $_SERVER['REQUEST_TIME']) {
			// $dbc->query('DELETE FROM auth_context_secret WHERE id = ?', $chk['id']);
			// _exit_html('<h1>Invalid Token [CAO#028]</h2><p>The link you followed has expired</p>', 400);
		// }

		$data = json_decode($chk['meta'], true);
		if (empty($data)) {
			// $dbc->query('DELETE FROM auth_context_secret WHERE id = ?', $chk['id']);
			return $RES->withRedirect('/done?e=cao077');
		}
		// var_dump($data);

		switch ($data['action']) {
		case 'account-create':

			return $this->accountCreate($RES, $data);

		case 'email-verify':

			$arg = [
				'action' => 'email-verify-save',
				'contact' => $data['contact'],
			];
			$arg = json_encode($arg);
			$arg = _encrypt($arg, $_SESSION['crypt-key']);

			return $RES->withRedirect('/account/verify?_=' . $arg);

		case 'password-reset':
			$val = [
				'contact' => $data['contact']
			];
			$val = json_encode($val);
			$x = _encrypt($val, $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/password?_=' . $x);
		}

		$data = [];
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
			_exit_text('Invalid [CAO#073]');
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

		$dbc = $this->_container->DB;
		$Contact = $dbc->fetchRow('SELECT id, username FROM auth_contact WHERE username = ?', [ $username ]);
		if (empty($Contact)) {
			return $RES->withRedirect('/done?e=cao100');
		}


		// Generate Authentication Hash
		$acs = [];
		$acs['id'] = \Edoceo\Radix\ULID::generate();
		$acs['meta'] = json_encode(array(
			'action' => 'password-reset',
			'contact' => $Contact,
			'geoip' => geoip_record_by_name($_SERVER['REMOTE_ADDR']),
		));
		$acs['code'] = base64_encode_url(hash('sha256', openssl_random_pseudo_bytes(256), true));
		$dbc->insert('auth_context_secret', $acs);


		// Use CIC to Send
		$cic = new \OpenTHC\Service\OpenTHC('cic');
		$arg['to'] = $Contact['username'];
		$arg['file'] = 'sso/password-reset.tpl';
		$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
		$arg['data']['mail_subj'] = 'Password Reset Request';
		$arg['data']['auth_hash'] = $acs['code'];

		$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);
		// var_dump($res);

		return $RES->withRedirect('/done?e=cao100');

	}
}
