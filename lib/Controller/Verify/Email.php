<?php
/**
 * Verify Email
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

use OpenTHC\Contact;

class Email extends \OpenTHC\SSO\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Email';

		$act = $this->loadTicket();

		$data['Contact'] = $act['contact'];
		$data['Contact']['email'] = $data['Contact']['username'];

		$data['verify_email'] = (0 == ($data['Contact']['flag'] & Contact::FLAG_EMAIL_GOOD));

		return $RES->write( $this->render('verify/email.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();

		switch ($_POST['a']) {
			case 'verify-email-save':
			case 'email-verify-save':
				return $this->emailVerifyConfirm($RES, $act['contact']['id']);
			case 'email-verify-send':
				return $this->emailVerifySend($RES, $act);
		}

		return $this->sendFailure($RES, [
			'error_code' => 'CVE-041',
			'fail' => 'Invalid Account',
		]);

	}

	/**
	 *
	 */
	function emailVerifyConfirm($RES, $contact_id)
	{
		$dbc = $this->_container->DBC_AUTH;

		// Set Flag
		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $contact_id,
			':f1' => Contact::FLAG_EMAIL_GOOD,
		];
		$dbc->query($sql, $arg);

		// Del Flag
		$sql = 'UPDATE auth_contact SET flag = flag & ~:f0::int WHERE id = :pk';
		$arg = [
			':pk' => $contact_id,
			':f0' => Contact::FLAG_EMAIL_WANT,
		];
		$dbc->query($sql, $arg);

		return $RES->withRedirect('/verify?' . http_build_query($_GET));

		// $data = $this->data;
		// $data['Page']['title'] = 'Email Verification';
		// $data['info'] = 'Email address has been validated';
		// if (empty($_SESSION['Contact'])) {
		// 	$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/open">Sign In <i class="icon icon-arrow-right"></i></a></div>';
		// } else {
		// 	$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/init">Continue <i class="icon icon-arrow-right"></i></a></div>';
		// }

		// // Set Contact Model on Response
		// $RES = $RES->withAttribute('Contact', [
		// 	'id' => $ARG['contact']['id'],
		// 	'username' => $ARG['contact']['username'],
		// 	'flag' => Contact::FLAG_EMAIL_GOOD,
		// ]);

		// // @deprecated use ACT, is this even the right spot for it?
		// // Landed here from Password Reset?
		// // No prompt, just show verifications
		// if ('password-reset' == $ARG['source']) {
		// 	unset($ARG['intent']);
		// 	unset($ARG['source']);
		// 	$x = _encrypt(json_encode($ARG), $_SESSION['crypt-key']);
		// 	return $RES->withRedirect('/account/verify?_=' . $x);
		// }

		// $html = $this->render('done.php', $data);
		// return $RES->write($html);

	}

	/**
	 *
	 */
	function emailVerifySend($RES, $ARG)
	{
		$dbc = $this->_container->DBC_AUTH;

		$acs = [];
		$acs['id'] = _random_hash();
		$acs['meta'] = json_encode([
			'intent' => 'email-verify',
			'contact' => $ARG['contact'],
		]);
		$dbc->insert('auth_context_ticket', $acs);

		// Return/Redirect
		$ret_path = '/done';
		$ret_args = [
			'e' => 'CAV-228'
		];

		$RES = $RES->withAttribute('Auth_Context_Ticket', $act['id']);
		$RES = $RES->withAttribute('Contact', $ARG['contact']);

		// Test Mode
		if ('TEST' == getenv('OPENTHC_TEST')) {
			$ret_args['t'] = $acs['id'];
		}

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}

}
