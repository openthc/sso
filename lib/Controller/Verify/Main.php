<?php
/**
 * Verify Main Controller
 * Contact Name + Email => Region + Timezone => Phone => Company Name => License Name
 */

namespace App\Controller\Verify;

class Main extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$chk = $dbc_auth->fetchRow('SELECT expires_at, meta FROM auth_context_ticket WHERE id = :t', [ ':t' => $_GET['_']]);
		if (empty($chk['meta'])) {
			$dbc_auth->query('DELETE FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			return $RES->withRedirect('/done?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'cao066'
			]));
		}
		$act = json_decode($chk['meta'], true);

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

		$CT0 = $dbc_auth->fetchRow('SELECT id, flag, stat FROM auth_contact WHERE id = :ct0', [
			':ct0' => $act['contact']['id']
		]);

		if (0 == ($CT0['flag'] & \App\Contact::FLAG_EMAIL_GOOD)) {
			__exit_text('Verify Email');
			return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
		}

		if (empty($CT0['iso3166'])) {
			return $RES->withRedirect(sprintf('/verify/region?_=%s', $_GET['_']));
		}

		if (empty($CT0['tz'])) {
			__exit_text('Verify Timezone');
			return $RES->withRedirect(sprintf('/verify/region?_=%s#tz', $_GET['_']));
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
