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
		$file = 'page/auth/open.html';
		$data = $this->data;
		$data['Page']['title'] = 'Sign In';

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

		$data['auth_username'] = $_SESSION['email'];

		$data['auth_goto'] = $_GET['r'];
		if (!empty($data['auth_goto'])) {
			$data['auth_hint'] = '<p>You will sign in, and then authorize the application via <a href="https://oauth.net/2/" target="_blank">OAuth2</a></p>';
		}

		// Carry forward the Redirect Values
		// Can't this be handled by auth_goto?
		if (!empty($_GET['r'])) {
			$_SESSION['return-link'] = $_GET['r'];
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
			$sql = 'SELECT id, username, password FROM auth_contact WHERE username = :un';
			$arg = [ ':un' => $username ];
			$chk = $dbc->fetchRow($sql, $arg);

			if (empty($chk['id'])) {
				return $RES->withRedirect('/auth/open?e=cao093');
			}

			if (!password_verify($password, $chk['password'])) {
				return $RES->withRedirect('/auth/open?e=cao093');
			}

			// @todo Reset Whole Session Here
			// $_SESSION['crypt-key'] =
			$_SESSION['Contact'] = [
				'id' => $chk['id'],
				'username' => $chk['username'],
			];
			$_SESSION['Company'] = [];

			// $acl = new ACL($_SESSION['Contact']['username']);
			// $acl->setPolicyForUser('authn/init');
			// $acl->save();

			return $RES->withRedirect('/auth/init');

			break;
		}

		$data = $this->data;
		$data['Page']['title'] = 'Error';
		$RES = $this->_container->view->render($RES, 'page/done.html', $data);
		return $RES->withStatus(400);

	}
}
