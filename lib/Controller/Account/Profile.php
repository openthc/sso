<?php
/**
 * View your Own Account
 */

namespace App\Controller\Account;

class Profile extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$file = 'page/account/profile.html';

		$data = $this->data;
		$data['Page'] = [ 'title' => 'Account' ];
		if (empty($_SESSION['Contact']['id'])) {
			__exit_text('Invalid Session [CAP-017]', 403);
		}

		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// $dbc_auth->query('BEGIN');
		// $dbc_main->query('BEGIN');
		$C0 = $dbc_auth->fetchRow('SELECT * FROM auth_contact WHERE id = :ct0', [ ':ct0' => $_SESSION['Contact']['id'] ]);
		if (empty($C0['id'])) {
			__exit_text('Invalid Session [CAP-027]', 403);
		}
		$C1 = $dbc_main->fetchRow('SELECT * FROM contact WHERE id = :ct0', [ ':ct0' => $_SESSION['Contact']['id'] ]);
		if (empty($C1['id'])) {
			__exit_text('Invalid Session [CAP-031]', 403);
		}

		$data['Contact_Auth'] = $C0;
		$data['Contact_Base'] = $C1;

		// Company List
		$res = $dbc_auth->fetchAll('SELECT id, name FROM auth_company WHERE id IN (SELECT company_id FROM auth_company_contact WHERE contact_id = :ct0)', [
			':ct0' => $_SESSION['Contact']['id'],
		]);
		$data['company_list'] = $res;

		$svc_list = \OpenTHC\Config::get('openthc');
		// var_dump($svc_list); exit;

		// $data['service_list'] = [];

		// $data['service_list'][] = [
		// 	'name' => 'App',
		// 	'link' => 'https://app.openthc.dev/auth/open?a=oauth',
		// ];

		// $data['service_list'][] = [
		// 	'name' => 'Directory',
		// 	'link' => 'https://dir.openthc.dev/auth/open?a=oauth',
		// ];

		// $data['service_list'][] = [
		// 	'name' => 'Lab',
		// 	'link' => 'https://lab.openthc.dev/auth/open?a=oauth',
		// ];

		// $data['service_list'][] = [
		// 	'name' => 'B2B',
		// 	'link' => 'https://b2b.openthc.dev/auth/open?a=oauth',
		// ];

		// $data['service_list'][] = [
		// 	'name' => 'POS',
		// 	'link' => 'https://pos.openthc.dev/auth/open?a=oauth',
		// ];

		// $data['service_list'][] = [
		// 	'name' => 'OPS',
		// 	'link' => 'https://ops.openthc.dev/auth/open?a=oauth',
		// ];

		// $data['service_list'][] = [
		// 	'name' => 'CIC',
		// 	'link' => 'https://cic.openthc.dev/auth/open?a=oauth',
		// ];

		return $RES->write( $this->render($file, $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		switch ($_POST['a']) {
			case 'contact-phone-update':
				$dbc_main->query('UPDATE contact SET phone = :p1 WHERE id = :c0', [
					':c0' => $_SESSION['Contact']['id'],
					':p1' => _phone_e164($_POST['contact-phone'])
				]);
				break;
		}

		return $RES->withRedirect('/account');

	}

	function loadCompany()
	{

	}
}
