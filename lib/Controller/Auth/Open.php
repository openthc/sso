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
		// Well known actions
		// /.well-known/change-password redirect here
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

		// Normal Page Open
		$file = 'page/auth/open.html';
		$data = $this->data;
		$data['Page']['title'] = 'Sign In';

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

		return $this->_container->view->render($RES, $file, $data);

	}

	/**
	 * Auth Open POST Handler
	 */
	function post($REQ, $RES, $ARG)
	{
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

		switch (strtolower($_POST['a'])) {
		case 'sign in': // Sign In

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
