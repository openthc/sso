<?php
/**
 * Verify Main Controller
 * Contact Name + Email => Region + Timezone => Phone => Company Name => License Name
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

use OpenTHC\Contact;

use OpenTHC\SSO\Auth_Contact;

class Main extends \OpenTHC\SSO\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		if (empty($_SESSION['verify'])) {
			$_SESSION['verify'] = [
				'phone' => [
					'done' => true,
				],
				'company' => [],
			];
		}

		$act = $this->loadTicket();

		return $this->guessNextStep($RES, $act);

		return $this->sendFailure($RES, [
			'error_code' => 'CVM-032'
		]);

	}

	/**
	 * Guess next step of the verification process
	 */
	function guessNextStep($RES, $act_data)
	{
		$CT0 = new Auth_Contact(null, $act_data['contact']);

		$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act_data);

		// Verify Email
		if ( ! $CT0->hasFlag(Contact::FLAG_EMAIL_GOOD)) {
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $tok));
		}
		if ($CT0->hasFlag(Contact::FLAG_EMAIL_WANT)) {
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $tok));
		}

		// Verify Password
		if (empty($CT0['password'])) {
			return $RES->withRedirect(sprintf('/verify/password?_=%s', $tok));
		}

		// Verify Location
		if (empty($CT0['iso3166'])) {
			return $RES->withRedirect(sprintf('/verify/location?_=%s', $tok));
		}

		// Timezone
		if (empty($CT0['tz'])) {
			return $RES->withRedirect(sprintf('/verify/timezone?_=%s', $tok));
		}

		// Phone
		/*
		if (empty($_SESSION['verify']['phone']['done'])) {
			if ($CT0->hasFlag(Contact::FLAG_PHONE_WANT)) {
				return $RES->withRedirect(sprintf('/verify/phone?_=%s', $tok));
			}
			if ( ! $CT0->hasFlag(Contact::FLAG_PHONE_GOOD)) {
				return $RES->withRedirect(sprintf('/verify/phone?_=%s', $tok));
			}
		}
		*/

		// Company
		$dbc_auth = $this->_container->DBC_AUTH;
		$chk = $dbc_auth->fetchOne('SELECT count(id) FROM auth_company_contact WHERE contact_id = :ct0', [
			':ct0' => $CT0['id'],
		]);
		if (empty($chk)) {
			return $RES->withRedirect(sprintf('/verify/company?_=%s', $tok));
		}

		switch ($act_data['intent']) {
			case 'account-invite':
				// Company Lookup?
				$dbc_auth->query('UPDATE auth_contact SET flag = (flag | :f1::int), stat = 200 WHERE id = :ct0', [
					':f1' => 3,
					':ct0' => $CT0['id'],
				]);

				$RES = $RES->withAttribute('verify-done', true);
				$RES = $RES->withAttribute('Contact', $act_data['contact']);

				return $RES->withRedirect('/done?e=CVM-119');

				break;
		}

		// Update Contact Status
		$dbc = $this->_container->DBC_AUTH;
		$CT1 = new Auth_Contact($dbc, $act_data['contact']['id']);
		$CT1['stat'] = Contact::STAT_LIVE;
		$CT1->save('Account/Contact/Verify');

		$dbc->insert('log_event', [
			'contact_id' => $CT0['id'],
			'code' => 'Contact/Account/Live',
			'meta' => json_encode([
				'Contact' => $CT1,
				'_SESSION' => $_SESSION,
			]),
		]);

		$x = $CT1->toArray();
		unset($x['password']);
		$this->_container->RDB->publish('openthc/sso/account/verify/done', json_encode([
			'Contact' => $x,
			'Company' => $_SESSION['verify']['company'],
		]));

		$RES = $RES->withAttribute('verify-done', true);
		$RES = $RES->withAttribute('Contact', $act_data['contact']);

		return $RES->withRedirect('/done?e=CVM-119'); // Prompt to Sign-In
		return $RES->withRedirect('/done?e=CVM-130'); // Prompt to Wait for Activation

	}
}
