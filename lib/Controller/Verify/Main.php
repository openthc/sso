<?php
/**
 * Verify Main Controller
 * Contact Name + Email => Region + Timezone => Phone => Company Name => License Name
 */

namespace App\Controller\Verify;

class Main extends \App\Controller\Verify\Base
{
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
			case 'account-verify':
				return $this->guessNextStep($RES, $act);
				break;
			case 'contact-email-verify':
			case 'contact-phone-verify':
			// case 'contact-'
		}

		__exit_text('Invalid Request [CVM-032]', 400);

	}

	/**
	 *
	 */
	function guessNextStep($RES, $act)
	{
		$dbc_auth = $this->_container->DBC_AUTH;

		$CT0 = $dbc_auth->fetchRow('SELECT id, flag, stat, iso3166, tz FROM auth_contact WHERE id = :ct0', [
			':ct0' => $act['contact']['id']
		]);

		if (0 == ($CT0['flag'] & \App\Contact::FLAG_EMAIL_GOOD)) {
			__exit_text('Verify Email');
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}

		if (empty($CT0['iso3166'])) {
			unset($_SESSION['iso3166_1']);
			unset($_SESSION['iso3166_2']);
			return $RES->withRedirect(sprintf('/verify/location?_=%s', $_GET['_']));
		}

		if (empty($CT0['tz'])) {
			return $RES->withRedirect(sprintf('/verify/timezone?_=%s#tz', $_GET['_']));
		}

		if (0 == ($CT0['flag'] & \App\Contact::FLAG_PHONE_GOOD)) {
			__exit_text('Verify Phone');
			return $RES->withRedirect(sprintf('/verify/phone?_=%s', $_GET['_']));
		}

		// Company
		$chk = $dbc_auth->fetchOne('SELECT count(id) FROM auth_company_contact WHERE contact_id = :ct0', [
			':ct0' => $act['contact']['id'],
		]);
		if (empty($chk)) {
			__exit_text('Verify Company or Skip');
			return $RES->withRedirect(sprintf('/verify/company?_=%s', $_GET['_']));
		}

		// Verify Company Profile in Directory?
		// $chk = $dbc_auth->fetchOne('SELECT id, stat, flag, iso3316, tz FROM auth_company WHERE id = :cy0', [
		// 	':cy0' => $act['contact']['id'],
		// ]);
		// if (empty($chk)) {
		// 	__exit_text('Verify Company');
		// }

		__exit_text([
			'data' => $act,
			'meta' => [
				'detail' => 'Seems Fully Verified to Me'
			]
		]);
	}
}
