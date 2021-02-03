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
			case 'cao049':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid email, please use a proper email address</div>';
				break;
			case 'cao069':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid Password, must be at least 8 characters</div>';
				break;
			case 'cao093':
				$data['Page']['flash'] = '<div class="alert alert-danger">Invalid Username or Password</div>';
				break;
			case 'cap080':
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

			$file = 'page/auth/once-password-reset.html';

			return $this->_container->view->render($RES, $file, $data);

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
				$data['auth_hint'] = sprintf('<p>Sign in, and then authorize the service (<em>%s</em>) via <a href="https://oauth.net/2/" target="_blank">OAuth2</a></p>', $act['service']);
			}
		}

		// $data['auth_goto'] = $_GET['r'];
		// if (!empty($data['auth_goto'])) {
		// 	$data['auth_hint'] = '<p>You will sign in, and then authorize the application via <a href="https://oauth.net/2/" target="_blank">OAuth2</a></p>';
		// }
		$file = 'page/auth/open.html';

		return $this->_container->view->render($RES, $file, $data);

	}

	/**
	 * Auth Open POST Handler
	 */
	function post($REQ, $RES, $ARG)
	{
		// Clear Session
		unset($_SESSION['Contact']);
		unset($_SESSION['Company']);
		unset($_SESSION['License']);
		unset($_SESSION['Service']);

		switch (strtolower($_POST['a'])) {
		case 'password-reset-request':
			return $this->sendPasswordReset($RES);
			break;
		case 'sign in': // Sign In

			// Process Inputs
			$username = strtolower(trim($_POST['username']));
			$username = \Edoceo\Radix\Filter::email($username);
			if (empty($username)) {
				return $RES->withRedirect('/auth/open?e=cao049');
			}

			$_SESSION['email'] = $username;

			$password = trim($_POST['password']);
			if (empty($password) || (strlen($password) < 8) || (strlen($password) > 60)) {
				return $RES->withRedirect('/auth/open?e=cao069');
			}

			// Find Contact
			$dbc = $this->_container->DBC_AUTH;
			$sql = 'SELECT id, flag, username, password FROM auth_contact WHERE username = :un';
			$arg = [ ':un' => $username ];
			$chk = $dbc->fetchRow($sql, $arg);

			if (empty($chk['id'])) {
				return $RES->withRedirect('/auth/open?e=cao093');
			}

			if (!password_verify($password, $chk['password'])) {
				return $RES->withRedirect('/auth/open?e=cao093');
			}

			// Next Authentication Token
			$act_data = [
				'intent' => 'init',
				'contact' => [
					'id' => $chk['id'],
					'flag' => $chk['flag'],
					'stat' => 200,
					'username' => $chk['username'],
				],
				'company' => [],
				'company_list' => [],
				'service' => [], // inidicate the service here
			];

			// If we have a Prevous Auth-Ticket
			if (!empty($_GET['_'])) {
				$act_prev = $dbc->fetchOne('SELECT meta FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
				$act_prev = json_decode($act_prev, true);
				switch ($act_prev['intent']) {
					case 'oauth-authorize':
						$act_data['intent'] = $act_prev['intent'];
						$act_data['serivce'] = $act_prev['service'];
						$act_data['oauth-request'] = $act_prev['oauth-request'];
				}
			}

			// Company Lookup Stuff
			$act_data = $this->_init_company($dbc, $act_data, $chk['id']);

			// Create Next Ticket & Redirect
			$act = [];
			$act['id'] = _random_hash();
			$act['meta'] = json_encode($act_data);
			$dbc->insert('auth_context_ticket', $act);

			return $RES->withRedirect('/auth/init?_=' . $act['id']);

			break;
		}

		$data = $this->data;
		$data['Page']['title'] = 'Error';
		$RES = $this->_container->view->render($RES, 'page/done.html', $data);
		return $RES->withStatus(400);

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
			return $RES->withRedirect('/auth/open?a=password-reset&e=cao049');
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
			$arg['address_target'] = $Contact['username'];
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


	/**
	 * Initialize Company Data in $act_data & return
	 */
	function _init_company($dbc, $act_data, $contact_id)
	{
		// Company List
		$sql = <<<SQL
SELECT auth_company.id
, auth_company.name
, auth_company.cre
, auth_company_contact.stat
, auth_company_contact.created_at
FROM auth_company
JOIN auth_company_contact ON auth_company.id = auth_company_contact.company_id
WHERE auth_company_contact.contact_id = :c0
ORDER BY auth_company_contact.stat, auth_company_contact.created_at ASC
SQL;

		$arg = [ ':c0' => $contact_id ];
		$chk = $dbc->fetchAll($sql, $arg);

		$act_data['company_list'] = $chk;
		if (count($chk) == 1) {
			$act_data['company'] = $chk;
		}

		return $act_data;

	}
}
