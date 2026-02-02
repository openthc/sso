<?php
/**
 * Verify Phone
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

use OpenTHC\Contact;

class Phone extends \OpenTHC\SSO\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Phone';

		$act = $this->loadTicket();

		$data['Contact'] = $act['contact'];

		$data['contact_phone'] = $data['Contact']['phone'];
		if (!empty($_SESSION['verify']['phone']['e164'])) {
			$data['contact_phone'] = $_SESSION['verify']['phone']['e164'];
		}

		$data['verify_phone'] = (0 == ($data['Contact']['flag'] & Contact::FLAG_PHONE_GOOD));

		// Prompt for Phone Verify
		if (empty($_SESSION['verify']['phone']['done']) && !empty($data['verify_phone'])) {

			$data['verify_phone_tick'] = $_SESSION['verify']['phone']['tick'];

			if (!empty($_SESSION['verify']['phone']['code'])) {
				$data['verify_phone_code'] = true;
			}

			$data['verify_phone_warn'] = $_SESSION['verify']['phone']['warn'];

		}

		return $RES->getBody()->write( $this->render('verify/phone.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();

		switch ($_POST['a']) {
			case 'phone-verify-send':

				return $this->phoneVerifySend($RES, $act);

			case 'phone-verify-save':
			case 'phone-verify-skip':

				// Skip, if Tick Count Enough
				if ('phone-verify-skip' == $_POST['a']) {
					if ($_SESSION['verify']['phone']['tick'] > 1) {

						// Fake Something?
						$_SESSION['verify']['phone']['done'] = true;

						return $this->redirect(sprintf('/verify?_=%s', $_GET['_']));

					}
				}

				return $this->phoneVerifySave($RES, $act);

		}

		__exit_text('Invalid Request [CVP-057]', 400);

	}

	/**
	 * Save Phone Verification
	 */
	function phoneVerifySave($RES, $ARG)
	{
		$_POST['phone-verify-code'] = strtoupper($_POST['phone-verify-code']);

		// Code does not match
		if ($_SESSION['verify']['phone']['code'] != $_POST['phone-verify-code']) {

			unset($_SESSION['verify']['phone']['code']);
			unset($_SESSION['verify']['phone']['warn']);

			return $this->redirect('/verify/phone?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAV-205'
			]));

		}

		$dbc_auth = $this->dic->get('DBC_AUTH');
		$dbc_main = $this->dic->get('DBC_MAIN');

		// Update Phone on Base Contact
		$C0 = new Contact($dbc_main, $ARG['contact']['id']);
		$C0['stat'] = Contact::STAT_LIVE;
		$C0['phone'] = $_SESSION['verify']['phone']['e164'];
		$C0->setFlag(Contact::FLAG_PHONE_GOOD);
		$C0->delFlag(Contact::FLAG_PHONE_WANT);
		$C0->save();

		// Set Flag on Auth Contact
		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':f1' => Contact::FLAG_PHONE_GOOD,
		];
		$dbc_auth->query($sql, $arg);

		// Clear Flag on Auth Contact
		$sql = 'UPDATE auth_contact SET flag = flag & ~:f0::int WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':f0' => Contact::FLAG_PHONE_WANT,
		];
		$dbc_auth->query($sql, $arg);

		// @todo Create/Update Channel & Link to Contact

		$_SESSION['verify']['phone']['done'] = true;

		return $this->redirect(sprintf('/verify?_=%s', $_GET['_']));

	}


	/**
	 * Send the Phone Verification Text
	 */
	function phoneVerifySend($RES, $ARG)
	{
		unset($_SESSION['verify']['phone']['warn']);

		$_SESSION['verify']['phone'] = [
			'tick' => intval($_SESSION['verify']['phone']['tick']) + 1,
			'code' => substr(str_shuffle('ADEFHJKMNPRTWXY34679'), 0, 6),
			'e164' => _phone_e164($_POST['contact-phone'])
		];

		$ret_args = [
			'_' => $_GET['_'],
		];

		$RES = $RES->withAttribute('mode', 'phone-verify-send');

		// Test Mode
		if (is_test_mode()) {
			$ret_args['t'] = $_SESSION['verify']['phone']['code'];
		}

		return $this->redirect('/verify/phone?' . http_build_query($ret_args));

	}

}
