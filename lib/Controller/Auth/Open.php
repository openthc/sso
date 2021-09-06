<?php
/**
 * Authenticate
 */

namespace App\Controller\Auth;

use Edoceo\Radix\Filter;
use Edoceo\Radix\Session;

use App\Contact;

class Open extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Sign In';

		// Add Errors
		if (!empty($_GET['e'])) {
			switch ($_GET['e']) {
			case 'CAO-049':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid email, please use a proper email address</div>';
				break;
			case 'CAO-069':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid Password, must be at least 8 characters</div>';
				break;
			case 'CAO-093':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid Username or Password</div>';
				break;
			case 'CAP-080':
				$data['Page']['flash'] = '<div class="alert alert-info">Your Password has been updated, please sign-in to continue</div>';
				break;
			default:
				$data['Page']['flash'] = sprintf('<div class="alert alert-warning">Unexpected Error "%s"</div>', h($_GET['e']));
				break;
			}
		}

		// Well known actions
		// /.well-known/change-password redirect here
		switch ($_GET['a']) {
		case 'password-reset':

			$data['Page']['title'] = 'Password Reset';
			$data['auth_username'] = $_SESSION['email'];

			$cfg = \OpenTHC\Config::get('google');
			$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

			return $RES->write( $this->render('auth/once-password-reset.php', $data) );

			break;
		}

		// Inputs
		$data['auth_username'] = $REQ->getAttribute('auth_username');
		if (empty($data['auth_username'])) {
			$data['auth_username'] = $_SESSION['email'];
		}
		$data['auth_password'] = $REQ->getAttribute('auth_password');
		$data['auth_hint'] = $REQ->getAttribute('auth_hint');

		if (!empty($_GET['_'])) {
			$dbc = $this->_container->DBC_AUTH;
			$act = $dbc->fetchOne('SELECT meta FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			$act = json_decode($act, true);
			if (!empty($act['service'])) {
				$data['service'] = $act['service'];
				$data['auth_hint'] = sprintf('<p>Sign in, and then authorize the service (<em>%s</em>) via <a href="https://oauth.net/2/" target="_blank">OAuth2</a></p>', $act['service']);
			}
		}

		$RES = $RES->write( $this->render('auth/open.php', $data) );

		return $RES;

	}

	/**
	 * Auth Open POST Handler
	 */
	function post($REQ, $RES, $ARG)
	{
		\App\CSRF::verify($_POST['CSRF']);

		switch ($_POST['a']) {
		case 'password-reset-request':
			return $this->sendPasswordReset($RES);
			break;
		case 'account-open':
			return $this->openAccount($RES);
			break;
		}

		$data = $this->data;
		$data['Page']['title'] = 'Error';
		$data['fail'] = 'Invalid Request [CAO-095]';
		$html = $this->render('done.php', $data);
		$RES = $RES->write($html);
		return $RES->withStatus(400);

	}

	/**
	 *
	 */
	function openAccount($RES)
	{
		$_SESSION = [];

		$username = strtolower(trim($_POST['username']));
		$username = \Edoceo\Radix\Filter::email($username);
		if (empty($username)) {
			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-049'
			]));
		}
		$_SESSION['email'] = $username;

		$password = trim($_POST['password']);
		if (empty($password) || (strlen($password) < 8) || (strlen($password) > 60)) {
			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-069'
			]));
		}

		// Find Contact
		$dbc = $this->_container->DBC_AUTH;
		$sql = 'SELECT id, flag, stat, username, password FROM auth_contact WHERE username = :un';
		$arg = [ ':un' => $username ];
		$chk = $dbc->fetchRow($sql, $arg);

		if (empty($chk['id'])) {
			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-093'
			]));
		}

		if (!password_verify($password, $chk['password'])) {

			if (100 == $chk['stat']) {
				return $RES->withRedirect('/done?' . http_build_query([
					'e' => 'CAO-144'
				]));
			}

			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-093'
			]));
		}

		// Create Next Ticket & Redirect
		$act_data = [
			'intent' => 'account-open',
			'contact' => [
				'id' => $chk['id'],
				'flag' => $chk['flag'],
				'stat' => 200,
				'username' => $chk['username'],
			],
			'feature' => [
				'javascript' => $_POST['js-enabled'],
				'date-input' => $_POST['date-enabled'],
				'time-input' => $_POST['time-enabled']
			],
			'service' => $_GET['service'],
		];

		// If we have a Prevous Auth-Ticket
		if (!empty($_GET['_'])) {
			$act_prev = $dbc->fetchOne('SELECT meta FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			$act_prev = json_decode($act_prev, true);
			switch ($act_prev['intent']) {
				case 'oauth-authorize':
					$act_data['intent'] = $act_prev['intent'];
					$act_data['service'] = $act_prev['service'];
					$act_data['oauth-request'] = $act_prev['oauth-request'];
			}
		}

		$act = [];
		$act['id'] = _random_hash();
		$act['meta'] = json_encode($act_data);
		$dbc->insert('auth_context_ticket', $act);

		return $RES->withRedirect('/auth/init?_=' . $act['id']);
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
			return $RES->withRedirect('/auth/open?a=password-reset&e=CAO-049');
		}

		$dbc_auth = $this->_container->DBC_AUTH;
		$Contact = $dbc_auth->fetchRow('SELECT id, username FROM auth_contact WHERE username = :u0', [ ':u0' => $username ]);
		if (empty($Contact)) {
			return $RES->withRedirect('/done?e=CAO-100&l=173');
		}

		$_SESSION['email'] = $username;

		// Generate Authentication Hash
		$act = [];
		$act['id'] = _random_hash();
		$act['meta'] = json_encode(array(
			'intent' => 'password-reset',
			'contact' => $Contact,
		));
		$dbc_auth->insert('auth_context_ticket', $act);

		$ret_args = [
			'e' => 'CAO-100',
			'l' => '200',
		];
		$ret_path = '/done';

		if ($_ENV['test']) {

			// Pass Information Back
			$ret_args['r'] = '/auth/once';
			$ret_args['a'] = $act['id'];

		} else {

			$arg = [];
			$arg['address_target'] = $Contact['username'];
			$arg['file'] = 'sso/contact-password-reset.tpl';
			$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
			$arg['data']['mail_subject'] = 'Password Reset Request';
			$arg['data']['auth_context_ticket'] = $act['id'];

			// Use CIC to Send
			try {

				$cic = new \OpenTHC\Service\OpenTHC('cic');
				$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);

				$ret_args['s'] = 't';

			} catch (\Exception $e) {
				// Ignore
				$ret_args['e'] = 'CAO-236';
				$ret_args['s'] = 'f';
			}

		}

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}

}
