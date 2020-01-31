<?php
/**
 * Authenticate
 */

namespace App\Controller\Auth;

use Edoceo\Radix\Filter;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

use App\Contact;

class Open extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		unset($_SESSION['sign-in']); // @deprecated
		unset($_SESSION['sign-up']); // @deprecated

		$file = 'page/auth/open.html';
		$data = [];
		$data['Page'] = [ 'title' => 'Sign In' ];

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

	function post($REQ, $RES, $ARG)
	{
		Contact::setDB($this->_container->DB);

		$username = strtolower(trim($_POST['username']));
		$username = \Edoceo\Radix\Filter::email($username);
		if (empty($username)) {
			return $RES->withRedirect('/auth/open?e=cao049');
		}
		$_SESSION['email'] = $username;

		switch (strtolower($_POST['a'])) {
		case 'sign in': // Sign In

			// Auto Create User if they don't exist
			$chk = Contact::findByUsername($username);
			if (empty($chk)) {
				Session::flash('info', 'Please Create an Account to use OpenTHC');
				return $RES->withRedirect('/auth/create?e=cao063');
			}

			$password = trim($_POST['password']);
			if (empty($password) || (strlen($password) < 6) || (strlen($password) > 60)) {
				return $RES->withRedirect('/auth/open?e=cao069');
			}

			// Check
			$good = false;
			if ($p == $chk['password']) {
				$good = true;
			} elseif (password_verify($_POST['password'], $chk['password'])) {
				$good = true;
			}

			if (!$good) {
				$_SESSION['show-reset'] = true;
				return $RES->withRedirect('/auth/open?e=cao093');
			}

			$_SESSION['uid'] = $chk['id'];

			return $RES->withRedirect('/auth/init');

			break;
		}

		return $RES->withStatus(400);
	}
}
