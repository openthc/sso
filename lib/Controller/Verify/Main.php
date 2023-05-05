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
				'contact' => [],
				'email' => [],
				'password' => [],
				'iso3166-1' => [],
				'iso3166-2' => [],
				'tz' => [],
				'phone' => [],
				'company' => [],
				'license' => [],
			];
		}

		$act = $this->loadTicket();

		return $this->guessNextStep($RES, $act);

		__exit_text('Invalid Request [CVM-032]', 400);

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
		if (empty($_SESSION['verify']['phone']['done'])) {
			if ($CT0->hasFlag(Contact::FLAG_PHONE_WANT)) {
				return $RES->withRedirect(sprintf('/verify/phone?_=%s', $tok));
			}
			if ( ! $CT0->hasFlag(Contact::FLAG_PHONE_GOOD)) {
				return $RES->withRedirect(sprintf('/verify/phone?_=%s', $tok));
			}
		}

		// Company
		if (empty($_SESSION['verify']['company']['done'])) {

			$dbc_auth = $this->_container->DBC_AUTH;
			$chk = $dbc_auth->fetchOne('SELECT count(id) FROM auth_company_contact WHERE contact_id = :ct0', [
				':ct0' => $CT0['id'],
			]);

			if (empty($chk)) {
				return $RES->withRedirect(sprintf('/verify/company?_=%s', $tok));
			}

		}

		if (empty($_SESSION['verify']['license'])) {
			return $RES->withRedirect(sprintf('/verify/license?_=%s', $tok));
		}

		// Update Contact Status
		// $CT0['stat'] = Contact::STAT_LIVE;
		// $CT0->save();

		// $dbc_auth->insert('log_event', [
		// 	'contact_id' => $CT0['id'],
		// 	'code' => 'Contact/Account/Live',
		// 	'meta' => json_encode($_SESSION),
		// ]);

		// // pass back to /auth/init with same token
		// return $RES->withRedirect(sprintf('/auth/init?_=%s', $tok));

		$ops = new \OpenTHC\Service\OpenTHC('ops');
		$res = $ops->post('/webhook/openthc', [
			'action' => 'account-verify-complete',
			'contact' => $act_data['contact']['id'],
			'session' => $_SESSION,
		]);

		return $RES->withRedirect('/verify/done');

	}
}
