<?php
/**
 * Verify Company
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

class Company extends \OpenTHC\SSO\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Company';

		$act = $this->loadTicket();

		return $RES->write( $this->render('verify/company.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();
		$dbc = $this->_container->DBC_AUTH;

		switch ($_POST['a']) {
			case 'company-request':
			case 'company-skip':
				$CR0 = [
					'name' => ($_POST['company-name'] ?? $act['contact']['email']),
					'iso3166' => $act['iso3166'],
				];
				$LR0 = [
					'code' => $_POST['license-code'],
					'type' => $_POST['license-type'],
					'iso3166' => $act['iso3166'],
				];
				$RES = $RES->withAttribute('Company', $CR0);
				$RES = $RES->withAttribute('Contact', $act['contact']);
				$RES = $RES->withAttribute('License', $LR0);

				$dbc->insert('log_event', [
					'contact_id' => $ARG['contact']['id'],
					'code' => 'Contact/Company/Request',
					'meta' => json_encode($_SESSION),
				]);

				return $RES->withRedirect('/verify/done');

				break;
			/*
			case 'company-save':

				// Double Check with a SoundEx lookup?
				// And a Reg-Ex Lookup?
				// $dir = new \OpenTHC\Service\OpenTHC('dir');
				// $chk = $dir->get('/api/company?q=%s' . rawurlencode($_POST['company-name']));
				// switch ($chk['code']) {
				// 	case 200:
				// 		break;
				// }
				// var_dump($chk);

				$CY0 = [
					'id' => _ulid(),
					// 'hash' => '-',
					'name' => $_POST['company-name'],
					'stat' => 200,
					'flag' => 3,
					'iso3166' => $act['contact']['iso3166'],
					'tz'=> $act['contact']['tz'],
				];

				$this->save_company($CY0, $act['contact']);
				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));

				break;

			case 'company-skip':

				$CY0 = [
					'id' => _ulid(),
					'name' => $act['contact']['name'],
					'stat' => 200, // $act['contact']['stat'],
					'flag' => 3, // $act['contact']['flag'],
					'iso3166' => $act['contact']['iso3166'],
					'tz'=> $act['contact']['tz'],
				];

				$this->save_company($CY0, $act['contact']);
				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
			*/

		}

		__exit_text('Invalid Request [CVC-056]', 400);
	}

	/**
	 * Save the Company and link the Contact
	 */
	function save_company($CY0, $CT0)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		$dbc_auth->insert('auth_company', $CY0);

		$CY0['hash'] = '-';
		$dbc_main->insert('company', $CY0);

		// Link To Company
		$dbc_auth->insert('auth_company_contact', [
			'company_id' => $CY0['id'],
			'contact_id' => $CT0['id'],
		]);

	}
}
