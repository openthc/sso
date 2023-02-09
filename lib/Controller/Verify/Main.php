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
				'password',
				'iso3166-1',
				'iso3166-2',
				'tz',
				'phone',
				'company',
				'license',
			];
		}

		$act = $this->loadTicket();

		switch ($act['intent']) {
			case 'account-create':
				return $this->guessNextStep($RES, $act);
				break;
		}

		__exit_text('Invalid Request [CVM-032]', 400);

	}

	/**
	 * Guess next step of the verification process
	 */
	function guessNextStep($RES, $act_data)
	{
		$dbc_auth = $this->_container->DBC_AUTH;

		$CT0 = new Auth_Contact($dbc_auth, $act_data['contact']);

		// Verify Email
		if ( ! $CT0->hasFlag(Contact::FLAG_EMAIL_GOOD)) {
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}
		if ($CT0->hasFlag(Contact::FLAG_EMAIL_WANT)) {
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}

		// Verify Password
		if (empty($CT0['password'])) {
			return $RES->withRedirect(sprintf('/verify/password?_=%s', $_GET['_']));
		}

		// Verify Location
		if (empty($CT0['iso3166'])) {
			return $RES->withRedirect(sprintf('/verify/location?_=%s', $_GET['_']));
		}

		// Timezone
		if (empty($CT0['tz'])) {
			return $RES->withRedirect(sprintf('/verify/timezone?_=%s', $_GET['_']));
		}

		// Phone
		if ( ! $CT0->hasFlag(Contact::FLAG_PHONE_GOOD)) {
			return $RES->withRedirect(sprintf('/verify/phone?_=%s', $_GET['_']));
		}
		if ($CT0->hasFlag(Contact::FLAG_PHONE_WANT)) {
			return $RES->withRedirect(sprintf('/verify/phone?_=%s', $_GET['_']));
		}

		// Company
		$chk = $dbc_auth->fetchOne('SELECT count(id) FROM auth_company_contact WHERE contact_id = :ct0', [
			':ct0' => $CT0['id'],
		]);
		if (empty($chk)) {
			return $RES->withRedirect(sprintf('/verify/company?_=%s', $_GET['_']));
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
		// return $RES->withRedirect(sprintf('/auth/init?_=%s', $_GET['_']));

		return $RES->withRedirect('/verify/done');

	}
}
