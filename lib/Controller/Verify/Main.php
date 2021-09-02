<?php
/**
 * Verify Main Controller
 * Contact Name + Email => Region + Timezone => Phone => Company Name => License Name
 */

namespace App\Controller\Verify;

use App\Contact;

class Main extends \App\Controller\Verify\Base
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
			case 'account-open':
			case 'oauth-authorize':
				return $this->guessNextStep($RES, $act);
				break;
		}

		__exit_text('Invalid Request [CVM-032]', 400);

	}

	/**
	 *
	 */
	function guessNextStep($RES, $act_data)
	{
		$dbc_auth = $this->_container->DBC_AUTH;

		$CT0 = $dbc_auth->fetchRow('SELECT id, password, flag, stat, iso3166, tz FROM auth_contact WHERE id = :ct0', [
			':ct0' => $act_data['contact']['id']
		]);

		// Verify Email
		if (0 == ($CT0['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}
		// Want to Re-Verify?
		if (0 != ($CT0['flag'] & Contact::FLAG_EMAIL_WANT)) {
			// Can I update the Token Easily?
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}

		// Verify Password
		if (empty($CT0['password'])) {
			return $RES->withRedirect(sprintf('/verify/password?_=%s', $_GET['_']));
		}

		// Verify Location
		if (empty($CT0['iso3166'])) {
			return $RES->withRedirect(sprintf('/verify/location?_=%s', $_GET['_']));
		// } else {
			// Load It Up?
			// $_SESSION['iso3166'] = $CT0['iso3166'];
		}

		if (empty($CT0['tz'])) {
			return $RES->withRedirect(sprintf('/verify/timezone?_=%s', $_GET['_']));
		}

		if (0 == ($CT0['flag'] & Contact::FLAG_PHONE_GOOD)) {
			return $RES->withRedirect(sprintf('/verify/phone?_=%s', $_GET['_']));
		}

		// Company
		$chk = $dbc_auth->fetchOne('SELECT count(id) FROM auth_company_contact WHERE contact_id = :ct0', [
			':ct0' => $act_data['contact']['id'],
		]);
		if (empty($chk)) {
			return $RES->withRedirect(sprintf('/verify/company?_=%s', $_GET['_']));
		}

		// Verify Company Profile in Directory?
		// $chk = $dbc_auth->fetchOne('SELECT id, stat, flag, iso3316, tz FROM auth_company WHERE id = :cy0', [
		// 	':cy0' => $act_data['contact']['id'],
		// ]);
		// if (empty($chk)) {
		// 	__exit_text('Verify Company');
		// }

		// Update Contact Status
		$dbc_auth->query('UPDATE auth_contact SET stat = :s1 WHERE id = :pk AND stat != :s1', [
			':pk' => $CT0['id'],
			':s1' => Contact::STAT_LIVE
		]);

		$dbc_auth->insert('log_event', [
			'contact_id' => $data['contact']['id'],
			'code' => 'Contact/Account/Live',
			'meta' => json_encode($_SESSION),
		]);

		// pass back to /auth/init with same token
		return $RES->withRedirect(sprintf('/auth/init?_=%s', $_GET['_']));

	}
}
