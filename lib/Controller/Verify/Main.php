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
			case 'account-open':
			case 'account-verify':
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

		// Need Email
		if (0 == ($CT0['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}

		// Want to Re-Verify?
		if (0 != ($CT0['flag'] & Contact::FLAG_EMAIL_WANT)) {
			// Can I update the Token Easily?
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}

		// if (empty($CT0['password']) || ('NONE' == substr($CT0['password'], 0, 4))) {
		// 	// Needs Password Reset
		// 	__exit_text('Password Reset Here, Redirect to ./password', 501);
		// 	return $RES->withRedirect(sprintf('/account/password?_=%s', $_GET['_']));
		// }

		if (empty($CT0['iso3166'])) {
			unset($_SESSION['iso3166_1']);
			unset($_SESSION['iso3166_2']);
			return $RES->withRedirect(sprintf('/verify/location?_=%s', $_GET['_']));
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

		// pass to /auth/init
		$act_data['intent'] = 'account-open';
		$act1 = new \App\Auth_Context_Ticket($dbc_auth);
		$act1->create($act_data);

		return $RES->withRedirect(sprintf('/auth/init?_=%s', $act1['id']));

	}
}
