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

			if (!preg_match('/^([\w\-]{32,128})$/i', $_GET['_'], $m)) {
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'detail' => 'Invalid Request [CAO-022]' ]
				], 400);
			}

			$dbc = $this->_container->DBC_AUTH;
			$act = $dbc->fetchOne('SELECT meta FROM auth_context_ticket WHERE id = :t', [ ':t' => $_GET['_']]);
			$act = json_decode($act, true);
			$act['intent'] = $act['intent'] ?: $act['action'];
			switch ($act['intent']) {
				case 'email-verify':
					$arg = [
						'action' => 'email-verify-save',
						'contact' => $act['contact'],
					];
					$arg = json_encode($arg);
					$arg = _encrypt($arg, $_SESSION['crypt-key']);

					return $RES->withRedirect('/account/verify?_=' . $arg);

				break;
				case 'init':
				case 'oauth-migrate':
					// OK
					return $RES->withJSON($act);
			}

		}

		if (empty($_GET['a'])) {
			__exit_text('Invalid Request [CAO-047]', 400);
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
			__exit_text('Invalid Request [CAO-071]', 400);
		}

		$dbc_auth = $this->_container->DBC_AUTH;

		$act = $dbc_auth->fetchRow('SELECT * FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['a'] ]);
		if (empty($act)) {
			return $RES->withRedirect('/done?e=cao066');
		}
		// if (strtotime($act['ts_expires']) < $_SERVER['REQUEST_TIME']) {
			// $dbc->query('DELETE FROM auth_context_ticket WHERE id = :t0', $act['id']);
			// __exit_html('<h1>Invalid Ticket [CAO#028]</h2><p>The link you followed has expired</p>', 400);
		// }
		$chk = json_decode($act['meta'], true);
		if (empty($chk)) {
			$dbc_auth->query('DELETE FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $act['id'] ]);
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
				'source' => 'email', // @deprecated use the parameter 'next'
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

		return $RES->withStatus(400);

	}

	/**
	 *
	 */
	private function accountCreate($RES, $data)
	{
		$dbc = $this->_container->DBC_AUTH;

		// Update Contact
		$email = $data['account']['contact']['email'];
		$chk = $dbc->fetchOne('SELECT id FROM auth_contact WHERE username = :u0', [ ':u0' => $email ]);
		if (empty($chk)) {
			__exit_text('Invalid [CAO-073]', 400);
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

		$arg = [
			'e' => 'cao073',
			'_' => _encrypt($val, $_SESSION['crypt-key']),
		];

		return $RES->withRedirect('/done?' . http_build_query($arg));

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

		$dbc_auth = $this->_container->DBC_AUTH;
		$Contact = $dbc_auth->fetchRow('SELECT id, username FROM auth_contact WHERE username = :u0', [ ':u0' => $username ]);
		if (empty($Contact)) {
			return $RES->withRedirect('/done?e=cao100&l=173');
		}

		// Generate Authentication Hash
		$act = [];
		$act['id'] = _random_hash();
		$act['meta'] = json_encode(array(
			'action' => 'password-reset',
			'intent' => 'password-reset',
			'contact' => $Contact,
			'geoip' => geoip_record_by_name($_SERVER['REMOTE_ADDR']),
		));
		$dbc_auth->insert('auth_context_ticket', $act);

		$ret_args = [
			'e' => 'cao100',
			'l' => '200',
		];
		$ret_path = '/done';

		if ($_ENV['test']) {

			// Pass Information Back
			// Test Runner has to parse the Location URL
			$ret_args['r'] = sprintf('https://%s/auth/once', $_SERVER['SERVER_NAME']);
			$ret_args['a'] = $act['id'];

		} else {

			$arg = [];
			$arg['to'] = $Contact['username'];
			$arg['file'] = 'sso/contact-password-reset.tpl';
			$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
			$arg['data']['mail_subj'] = 'Password Reset Request';
			$arg['data']['auth_context_ticket'] = $act['id'];

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
