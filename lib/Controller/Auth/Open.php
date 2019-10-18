<?php
/**
 * Sign In
 */

 namespace App\Controller\Auth;

use Edoceo\Radix;
use Edoceo\Radix\Filter;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

class Open extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		unset($_SESSION['sign-in']); // @deprecated
		unset($_SESSION['sign-up']); // @deprecated

		$file = 'page/auth/open.html';
		$data = [];
		$data['Page'] = [ 'title' => 'Sign In' ];

		$data['auth_username'] = $_SESSION['email'];

		$data['auth_goto'] = $_GET['r'];
		$data['auth_hint'] = '<p>You will sign in, and then authorize the application via <a href="https://oauth.net/2/" target="_blank">OAuth2</a></p>';

		// Carry forward the Redirect Values
		// Can't this be handled by auth_goto?
		if (!empty($_GET['r'])) {
			$_SESSION['return-link'] = $_GET['r'];
		}

		return $this->_container->view->render($RES, $file, $data);

	}

	function post($REQ, $RES, $ARG)
	{
		switch (strtolower($_POST['a'])) {
		case 'email-confirm': // @todo should be via /once

			Auth::clear_session();

			$AU = AppUser::findByUsername($_POST['username']);
			if (empty($AU)) {
				Session::flash('fail', 'Failed to process confirmation request');
				return(0);
			}

			if ($AU->hasFlag(Contact::FLAG_DISABLED)) {
				Session::flash('fail', 'CAS#028: There is some issue with your account, please contact support');
				return(0);
			}

			// Auth Link
			$ah = new Auth_Hash();
			$ah['json'] = json_encode(array(
				'uid' => $AU['id'],
				'action' => 'email-confirm',
			));
			$ah['hash'] = sha1(serialize($AU) . serialize($ah));
			$ah->save();

			putenv("MAIL_RCPT={$AU['username']}");
			putenv("MAIL_HASH={$ah['hash']}");
			$cmd = (APP_ROOT . '/bin/mail-auth-mail-confirm.php');
			shell_exec("$cmd >>/tmp/mail-auth-mail-confirm.log 2>&1 &");

			Session::flash('info', 'An Email Confirmation message has been sent to your address');

			break;

		case 'reset':

			$_POST['username'] = trim($_POST['username']);
			if (!empty($_POST['username'])) {
				$_SESSION['email'] = $_POST['username'];
			}

			Radix::redirect('/auth/reset');

			break;

		case 'sign in': // Sign In

			// New User
			$u = strtolower(trim($_POST['username']));
			$p = trim($_POST['password']);

			$u = Filter::email($u);
			if (empty($u)) {
				Session::flash('fail', 'Invalid email, please use a proper email address');
				return $RES->withRedirect('/auth/open');
			}
			$_SESSION['email'] = $u;

			// Auto Create User if they don't exist
			$chk = AppUser::findByUsername($u);
			if (empty($chk)) {
				// Bounce to Sign-Up
				Session::flash('info', 'Please Create an Account to use OpenTHC');
				Radix::redirect('/auth/sign-up');
			}
			$_SESSION['email'] = $u;

			if (empty($p) || (strlen($p) < 6) || (strlen($p) > 60)) {
				Session::flash('fail', 'Invalid Password');
				return $RES->withRedirect('/auth/open');
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
				Session::flash('fail', 'Invalid username or password');
				return $RES->withRedirect('/auth/open');
			}

			$_SESSION['uid'] = $chk['id'];

			Radix::redirect('/auth/init-session');

			break;
		}

		return $RES->withStatus(400);
	}
}
