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

		$RES->getBody()->write( $this->render('verify/email.php', $data) );

		return $RES;

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
		$dbc_auth = $this->dic->get('DBC_AUTH');

		// Set Flag
		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $contact_id,
			':f1' => Contact::FLAG_EMAIL_GOOD,
		];
		$dbc_auth->query($sql, $arg);

		// Del Flag
		$sql = 'UPDATE auth_contact SET flag = flag & ~:f0::int WHERE id = :pk';
		$arg = [
			':pk' => $contact_id,
			':f0' => Contact::FLAG_EMAIL_WANT,
		];
		$dbc_auth->query($sql, $arg);

		return $this->redirect('/verify?' . http_build_query($_GET));

	}

	/**
	 *
	 */
	function emailVerifySend($RES, $ARG)
	{
		$dbc_auth = $this->dic->get('DBC_AUTH');

		$acs = [];
		$acs['id'] = _random_hash();
		$acs['meta'] = json_encode([
			'intent' => 'email-verify',
			'contact' => $ARG['contact'],
		]);
		$dbc_auth->insert('auth_context_ticket', $acs);

		// Return/Redirect
		$ret_path = '/done';
		$ret_args = [
			'e' => 'CAV-228'
		];

		$RES = $RES->withAttribute('Auth_Context_Ticket', $act['id']);
		$RES = $RES->withAttribute('Contact', $ARG['contact']);

		// Test Mode
		if (is_test_mode()) {
			$ret_args['t'] = $acs['id'];
		}

		return $this->redirect($ret_path . '?' . http_build_query($ret_args));

	}

}
