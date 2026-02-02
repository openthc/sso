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
			return $this->redirect(sprintf('/verify/email?_=%s', $tok));
		}
		if ($CT0->hasFlag(Contact::FLAG_EMAIL_WANT)) {
			return $this->redirect(sprintf('/verify/email?_=%s', $tok));
		}

		// Verify Password
		if (empty($CT0['password'])) {
			return $this->redirect(sprintf('/verify/password?_=%s', $tok));
		}

		// Verify Location
		if (empty($CT0['iso3166'])) {
			return $this->redirect(sprintf('/verify/location?_=%s', $tok));
		}

		// Timezone
		if (empty($CT0['tz'])) {
			return $this->redirect(sprintf('/verify/timezone?_=%s', $tok));
		}

		// Phone
		/*
		if (empty($_SESSION['verify']['phone']['done'])) {
			if ($CT0->hasFlag(Contact::FLAG_PHONE_WANT)) {
				return $this->redirect(sprintf('/verify/phone?_=%s', $tok));
			}
			if ( ! $CT0->hasFlag(Contact::FLAG_PHONE_GOOD)) {
				return $this->redirect(sprintf('/verify/phone?_=%s', $tok));
			}
		}
		*/

		$dbc_auth = $this->dic->get('DBC_AUTH');

		// Company
		// Not Required at the Moment
		// $chk = $dbc_auth->fetchOne('SELECT count(id) FROM auth_company_contact WHERE contact_id = :ct0', [
		// 	':ct0' => $CT0['id'],
		// ]);
		// if (empty($chk)) {
		// 	return $this->redirect(sprintf('/verify/company?_=%s', $tok));
		// }

		switch ($act_data['intent']) {
			case 'account-invite':

				// Update Contact Status
				$dbc_auth->query('UPDATE auth_contact SET flag = (flag | :f1::int), stat = 200 WHERE id = :ct0', [
					':f1' => 3,
					':ct0' => $CT0['id'],
				]);

				$RES = $RES->withAttribute('verify-done', true);
				$RES = $RES->withAttribute('Contact', $act_data['contact']);

				$_SESSION['auth-open-email'] = $act_data['contact']['username'];

				return $this->redirect('/done?e=CVM-119');

				break;
		}

		// Update Contact Status
		$CT1 = new Auth_Contact($dbc_auth, $act_data['contact']['id']);
		$CT1['stat'] = Contact::STAT_LIVE;
		$CT1->save('Account/Contact/Verify');

		$dbc_auth->insert('log_event', [
			'contact_id' => $CT0['id'],
			'code' => 'Contact/Account/Live',
			'meta' => json_encode([
				'Contact' => $CT1,
				'_SESSION' => $_SESSION,
			]),
		]);

		$x = $CT1->toArray();
		unset($x['password']);
		$this->_container->RDB->publish('sso', json_encode([
			'event' => 'account/verify/done',
			'Contact' => $x,
			'_SESSION' => $_SESSION,
		]));

		$RES = $RES->withAttribute('verify-done', true);
		$RES = $RES->withAttribute('Contact', $act_data['contact']);

		$_SESSION['auth-open-email'] = $act_data['contact']['username'];

		return $this->redirect('/done?e=CVM-119'); // Prompt to Sign-In
		return $this->redirect('/done?e=CVM-130'); // Prompt to Wait for Activation

	}
}
