<?php
/**
 * Initialize an Authenticated Session
 */

namespace App\Controller\Auth;

use Edoceo\Radix\Session;

use App\Contact;

class Init extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		Contact::setDB($this->_container->DB);

		$U = new Contact($_SESSION['uid']);

		if (empty($U['id'])) {
			_exit_text('Unexpected account issue, please contact support [CAI#016]', 400);
		}

		// if (!$chk->hasFlag(Contact::FLAG_MAILGOOD)) {
		// 	$_SESSION['show-email-confirm'] = true;
		// 	Session::flash('fail', 'You must confirm your email before using OpenTHC');
		// 	return(0);
		// }

		if ($U->hasFlag(Contact::FLAG_DISABLED)) {
			_exit_text('There is some issue with your account, please contact support [CAI#020]', 400);
		}

		// Save State
		$_SESSION['uid'] = $U['id'];
		$_SESSION['gid'] = $U['company_id'];
		$_SESSION['email'] = $U['username'];

		// Update Sign-In Time - User
		// $sql = 'UPDATE auth_contact SET ts_sign_in = now() WHERE id = ?';
		// $arg = array($_SESSION['uid']);
		// $this->_container->DB->query($sql, $arg);

		// return $RES->withRedirect('/profile/verify');

		// Implement your own Here, or use Middleware
		$cfg = \OpenTHC\Config::get('openthc_app');
		$ret = $cfg['url'];

		if (!empty($_SESSION['return-link'])) {
			$ret = $_SESSION['return-link'];
			unset($_SESSION['return-link']);
		}

		// _exit_html("<a href='$ret'>$ret</a>");
		return $RES->withRedirect($ret);

	}
}
