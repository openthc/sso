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
		$data['company-email'] = $act['contact']['email'];
		$data['company-phone'] = $act['contact']['phone'];

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

				// pass thru
			case 'company-skip':

				$CY0 = [
					'id' => _ulid(),
					'name' => $_POST['company-name'] ?: $act['contact']['email'],
					'iso3166' => $act['contact']['iso3166'],
					'tz'=> $act['contact']['tz'],
					// 'cre_meta' => json_encode($_POST),
				];

				$dbc->insert('auth_company', $CY0);

				// Link To Company
				$dbc->insert('auth_company_contact', [
					'company_id' => $CY0['id'],
					'contact_id' => $act['contact']['id'],
				]);

				// Account Sign-Up Meta
				$RES = $RES->withAttribute('Company', $CY0);
				$RES = $RES->withAttribute('Contact', $act['contact']);

				// syslog(LOG_NOTICE, )
				$dbc->insert('log_event', [
					'contact_id' => $act['contact']['id'],
					'code' => 'Verify/Company/Create',
					'meta' => json_encode($_SESSION),
				]);

				$_SESSION['verify']['company']['done'] = true;

				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));

				break;

		}

		__exit_text('Invalid Request [CVC-056]', 400);
	}

}
