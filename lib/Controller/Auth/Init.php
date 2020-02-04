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
		$dbc = $this->_container->DB;
		$sql = 'SELECT id, company_id, username, flag FROM auth_contact WHERE id = :pk';
		$arg = [ ':pk' => $_SESSION['uid'] ];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#016]', 400);
		}

		// if (!$chk->hasFlag(Contact::FLAG_MAILGOOD)) {
		// 	$_SESSION['show-email-confirm'] = true;
		// 	Session::flash('fail', 'You must confirm your email before using OpenTHC');
		// 	return(0);
		// }
		if (0 == ($chk['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			// _exit_text('Validate Email!');
			// return $RES->withRedirect('/account/verify');
		}

		if (0 == ($chk['flag'] & Contact::FLAG_PHONE_GOOD)) {
			// _exit_text('Validate Phone!');
			// return $RES->withRedirect('/account/verify');
		}

		if (0 != ($chk['flag'] & Contact::FLAG_DISABLED)) {
			_exit_text('Invalid Account [CAI#038]', 403);
		}

		// Save State
		$_SESSION['gid'] = $U['company_id'];
		$_SESSION['email'] = $U['username'];

		if (!empty($_SESSION['return-link'])) {
			$ret = $_SESSION['return-link'];
			unset($_SESSION['return-link']);
		}
		if (empty($ret)) {
			$cfg = \OpenTHC\Config::get('openthc_app');
			$ret = $cfg['url'];
		}

		return $RES->withRedirect($ret);

	}
}
