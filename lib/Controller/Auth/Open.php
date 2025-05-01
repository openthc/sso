<?php
/**
 * Authenticate
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Auth;

use Edoceo\Radix\Filter;
use Edoceo\Radix\Session;

use OpenTHC\Contact;

use OpenTHC\CSRF;
use OpenTHC\SSO\Auth_Contact;

class Open extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		if ( ! empty($_GET['jwt'])) {
			return $this->handleJWT($RES, $_GET['jwt']);
		}

		$data = $this->data;
		$data['Page']['title'] = 'Sign In';

		// Add Errors
		if ( ! empty($_GET['e'])) {
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
			case 'CAO-153':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid Username or Password</div>';
				break;
			case 'CAO-159':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid Account Status</div>';
				break;
			case 'CAP-080':
				$data['Page']['flash'] = '<div class="alert alert-info">Your Password has been updated, please sign-in to continue</div>';
				break;
			default:
				$data['Page']['flash'] = sprintf('<div class="alert alert-warning">Unexpected Error "%s"</div>', h($_GET['e']));
				break;
			}
		}

		// Inputs
		$data['auth_username'] = $REQ->getAttribute('auth_username');
		if (empty($data['auth_username'])) {
			$data['auth_username'] = $_SESSION['auth-open-email'];
		}
		$data['auth_password'] = $REQ->getAttribute('auth_password');
		$data['auth_hint'] = $REQ->getAttribute('auth_hint');

		// Well known actions
		switch ($_GET['a']) {
		case 'password-reset':
			// /.well-known/change-password redirect here
			$data['Page']['title'] = 'Password Reset';
			$data['auth_username'] = $_SESSION['auth-open-email'];

			$cfg = \OpenTHC\Config::get('google');
			$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

			return $RES->write( $this->render('auth/once-password-reset.php', $data) );

			break;

		case 'switch-company':

			$Contact = $_SESSION['Contact'];
			$Company = $_SESSION['Company'];

			$act_data = [
				'intent' => 'account-open',
				'contact' => $Contact,
				'company' => $Company,
				'return' => $_GET['r'],
				'service' => $_GET['service'],
			];

			$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act_data);

			return $RES->withRedirect(sprintf('/auth/init?_=%s', $tok));

			break;

		case 'sso-migrate':

			$key = \OpenTHC\Config::get('openthc/app/sso-migrate-secret');
			$_GET['t'] = _decrypt($_GET['t'], $key);
			$act = json_decode($_GET['t']);

			$data['Page']['flash'] = '<div class="alert alert-warning">SSO migration in progress. Please sign-in once more.</div>';
			$data['auth_username'] = $act->username;
			$data['auth_password'] = $act->password;

		}

		// Get Service w/Caching
		if ( ! empty($_GET['service'])) {
			$data['service'] = $this->loadService($_GET['service']);
		}

		// Incoming Parameters
		if ( ! empty($_GET['_'])) {
			$act = \OpenTHC\SSO\Auth_Context_Ticket::get($_GET['_']);
			// intent == "oauth-authorize"
			if ( ! empty($act['service']) && ! empty($act['oauth-request'])) {
				$svc = $this->loadService($act['service']);
				if ( ! empty($svc)) {
					$data['auth_hint'] = sprintf('<p>Sign in, and then authorize the service (<em>%s</em>) via <a href="https://oauth.net/2/" target="_blank">OAuth2</a></p>'
						, $svc->name
					);
					$data['service'] = $svc;
				}
			} else {
				unset($_GET['_']);
			}
		}

		if (empty($data['auth_username'])) {
			$data['auth_username'] = $this->getEmailCookie();
		}

		$RES = $RES->write( $this->render('auth/open.php', $data) );

		return $RES;

	}

	/**
	 * Auth Open POST Handler
	 */
	function post($REQ, $RES, $ARG)
	{
		CSRF::verify($_POST['CSRF']);

		switch ($_POST['a']) {
		case 'password-reset-request':
			return $this->sendPasswordReset($RES);
			break;
		case 'account-open':
			return $this->openAccount($RES);
			break;
		}

		return $this->sendFailure($RES, [
			'error_code' => 'CAO-095',
			'fail' => 'Invalid Request',
		]);

	}

	/**
	 * Simply Verify the Account Loads and redirect to /auth/init
	 */
	function openAccount($RES)
	{
		$username = strtolower(trim($_POST['username']));
		$username = filter_var($username, FILTER_SANITIZE_EMAIL);
		$username = filter_var($username, FILTER_VALIDATE_EMAIL);
		// $username = \Edoceo\Radix\Filter::email($username);
		if (empty($username)) {
			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-049'
			]));
		}
		$this->setEmailCookie($username);

		$password = trim($_POST['password']);
		if (empty($password) || (strlen($password) < 8) || (strlen($password) > 60)) {
			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-069'
			]));
		}

		// Find Contact
		$dbc = $this->_container->DBC_AUTH;
		$sql = 'SELECT id, flag, stat, username, password FROM auth_contact WHERE username = :u0';
		$arg = [ ':u0' => $username ];
		$Contact = $dbc->fetchRow($sql, $arg);

		if (empty($Contact['id'])) {
			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-093'
			]));
		}

		if ( ! password_verify($password, $Contact['password'])) {
			return $RES->withRedirect('/auth/open?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-153'
			]));
		}

		// Ok, Pass Authentication Data to /auth/init
		$tok_data = [
			'intent' => 'account-open',
			'contact' => [
				'id' => $Contact['id'],
				'flag' => $Contact['flag'],
				'stat' => $Contact['stat'],
				'username' => $Contact['username'],
			],
			'feature' => [
				'javascript' => $_POST['js-enabled'],
				'date-input' => $_POST['date-enabled'],
				'time-input' => $_POST['time-enabled']
			],
			'return' => $_GET['r'],
			'service' => $_GET['service'],
		];

		// Detect if SSO MIgrate?
		if ('sso-migrate' == $_GET['a']) {
			$tok_data['option'] = 'sso-migrate';
		}

		// If we have a Prevous Auth-Ticket
		if ( ! empty($_GET['_'])) {

			$act_prev = \OpenTHC\SSO\Auth_Context_Ticket::get($_GET['_']);
			switch ($act_prev['intent']) {
				case 'oauth-authorize':
					$tok_data['intent'] = $act_prev['intent'];
					$tok_data['service'] = $act_prev['service'];
					$tok_data['oauth-request'] = $act_prev['oauth-request'];
					break;
				default:
					// Ignored
					break;
			}
		}

		$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($tok_data);

		return $RES->withRedirect(sprintf('/auth/init?_=%s', $tok));

	}


	/**
	 * Do the Password Reset Thing
	 */
	private function handleJWT($RES, $jwt)
	{
		$_SESSION = [];

		$dbc = $this->_container->DBC_AUTH;

		try {

			$jwt = \OpenTHC\JWT::decode_only($jwt);
			$key = $dbc->fetchOne('SELECT hash FROM auth_service WHERE id = :s0', [
				':s0' => $jwt->body->iss
			]);
			$jwt = \OpenTHC\JWT::decode($jwt, $key);

			// $_SESSION['Contact'] = [
			// 	'id' => $jwt['sub']
			// ];

			// $_SESSION['Company'] = [
			// 	'id' => $jwt['company']
			// ];

			// $_SESSION['License'] = [
			// 	'id' => $jwt['license']
			// ];

			// would like init to work from it's own JWT?
			// or it works from just a minimally populated session?
			// return $RES->withRedirect('/auth/init');

			$act = [];
			$act['id'] = _random_hash();
			$act['meta'] = json_encode([
				'intent' => 'account-open',
				'contact' => [
					'id' => $jwt['sub']
				]
			]);

			$dbc->insert('auth_context_ticket', $act);

			return $RES->withRedirect('/auth/init?_=' . $act['id']);

		} catch (\Exception $e) {
			return $this->sendFailure($RES, [
				'error_code' => 'CAO-243',
				'fail' => $e->getMessage(),
			], 500);
		}
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
			return $RES->withRedirect('/done?e=CAO-100');
		}

		$_SESSION['auth-open-email'] = $username;

		// Generate Authentication Hash
		$act = [];
		$act['id'] = _random_hash();
		$act['meta'] = json_encode(array(
			'intent' => 'password-reset',
			'contact' => $Contact,
			'ip' => $_SERVER['REMOTE_ADDR'],
		));
		$dbc_auth->insert('auth_context_ticket', $act);

		$ret_args = [
			'e' => 'CAO-200',
		];

		// Test Mode
		if (is_test_mode()) {
			$ret_args['t'] = $act['id'];
		}

		// Emit Webhook
		\OpenTHC\SSO\Facade\Webhook::emit('CONTACT/AUTH/RESET', [
			'auth' => [
				'token' => $act['id'],
			],
			'contact' => [
				'id' => $Contact['id'],
				'username' => $Contact['username'],
			]
		]);

		return $RES->withRedirect('/done?' . http_build_query($ret_args));

	}

	/**
	 *
	 */
	function loadService($s)
	{
		if (preg_match('/^[\w\.]{6,26}$/', $s)) {

			$otc = new \OpenTHC\Cache('sso');
			$svc = $otc->get(sprintf('service/%s', $s));
			if (empty($svc)) {
				$dbc = $this->_container->DBC_AUTH;
				$svc = $dbc->fetchRow('SELECT id, code, name FROM auth_service WHERE (id = :s0 OR code = :s0)', [
					':s0' => $s,
				]);
				if ( ! empty($svc['id'])) {
					$otc->set(sprintf('service/%s', $s), $svc);
				}
			}

			return $svc;
		}

	}

	/**
	 *
	 */
	function getEmailCookie()
	{
		$pk = \OpenTHC\Config::get('openthc/sso/public');
		$sk = \OpenTHC\Config::get('openthc/sso/secret');

		$val = $_COOKIE['email'];
		$val = \OpenTHC\Sodium::b64decode($val);
		$val = \OpenTHC\Sodium::decrypt($val, $sk, $pk);

		return $val;

	}

	/**
	 *
	 */
	function setEmailCookie($e)
	{
		$_SESSION['auth-open-email'] = $e;

		$pk = \OpenTHC\Config::get('openthc/sso/public');
		$sk = \OpenTHC\Config::get('openthc/sso/secret');

		$val = \OpenTHC\Sodium::encrypt($e, $sk, $pk);
		$val = \OpenTHC\Sodium::b64encode($val);

		$arg = [];
		$arg['expires'] = strtotime('+30 days');
		$arg['path'] = '/auth';
		// $arg['domain'];
		$arg['secure'] = true;
		$arg['httponly'] = true;
		$arg['samesite'] = 'lax';

		setcookie('email', $val, $arg);

	}

}
