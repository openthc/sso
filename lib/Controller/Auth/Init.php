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
			Session::flash('fail', 'Unexpected account issue, please contact support [CAI#016]');
			Radix::redirect('/auth');
		}

		// if (!$chk->hasFlag(Contact::FLAG_MAILGOOD)) {
		// 	$_SESSION['show-email-confirm'] = true;
		// 	Session::flash('fail', 'You must confirm your email before using OpenTHC');
		// 	return(0);
		// }

		if ($U->hasFlag(Contact::FLAG_DISABLED)) {
			Session::flash('fail', ': There is some issue with your account, please contact support [CAI#020]');
			Radix::redirect('/auth');
		}

		// Save State
		$_SESSION['uid'] = $U['id'];
		$_SESSION['gid'] = $U['company_id'];
		$_SESSION['email'] = $U['username'];

		// Update Sign-In Time - User
		// $sql = 'UPDATE auth_contact SET ts_sign_in = now() WHERE id = ?';
		// $arg = array($_SESSION['uid']);
		// $this->_container->DB->query($sql, $arg);

		return $RES->withRedirect('/setup');

	}
}
