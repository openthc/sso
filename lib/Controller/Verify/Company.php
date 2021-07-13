<?php
/**
 * Verify Company
 */

namespace App\Controller\Verify;

class Company extends \App\Controller\Verify\Base
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

		switch ($_POST['a']) {
			case 'company-save':

				// Double Check with a SoundEx lookup?
				$dir = new \OpenTHC\Service\OpenTHC('dir');
				$chk = $dir->get('/api/copmany?q=%s' . rawurlencode($_POST['company-name']));
				var_dump($chk);

				exit;
				// And a Reg-Ex Lookup?

				break;

			case 'company-skip':

				$CY0 = [
					'id' => _ulid(),
					'name' => $act['contact']['username'],
					'stat' => 200, // $act['contact']['stat'],
					'flag' => 3, // $act['contact']['flag'],
					'iso3166' => $act['contact']['iso3166'],
					'tz'=> $act['contact']['tz'],
				];

				$dbc_auth = $this->_container->DBC_AUTH;
				$dbc_main = $this->_container->DBC_MAIN;

				// $dbc_auth->insert('auth_company', $CY0);
				$CY0['hash'] = '-';
				$dbc_main->insert('company', $CY0);

				// Link To Company
				$dbc_auth->insert('auth_company_contact', [
					'company_id' => $CY0['id'],
					'contact_id' => $act['contact']['id'],
				]);

				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));

		}

		__exit_text('Invalid Request [CVC-056]', 400);
	}
}
