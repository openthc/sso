<?php
/**
 * View your Own Account
 *
 * SPDX-License-Identifier: MIT
 */

namespace App\Controller\Account;

class Profile extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page'] = [ 'title' => 'Account' ];
		if (empty($_SESSION['Contact']['id'])) {
			_exit_html_fail('<h1>Invalid Session [CAP-017]</h1>', 403);
		}

		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;


		$C0 = $dbc_auth->fetchRow('SELECT * FROM auth_contact WHERE id = :ct0', [ ':ct0' => $_SESSION['Contact']['id'] ]);
		if (empty($C0['id'])) {
			_exit_html_fail('<h1>Invalid Session [CAP-027]</h1>', 403);
		}
		$C1 = $dbc_main->fetchRow('SELECT * FROM contact WHERE id = :ct0', [ ':ct0' => $_SESSION['Contact']['id'] ]);
		if (empty($C1['id'])) {
			_exit_html_fail('<h1>Invalid Session [CAP-031]</h1>', 403);
		}

		$data['Contact_Auth'] = $C0;
		$data['Contact_Base'] = $C1;

		// Company List
		$res = $dbc_auth->fetchAll('SELECT id, name FROM auth_company WHERE id IN (SELECT company_id FROM auth_company_contact WHERE contact_id = :ct0)', [
			':ct0' => $_SESSION['Contact']['id'],
		]);
		$data['company_list'] = $res;

		// Active Service List
		// $res = $dbc_auth->fetchAll('SELECT id, name FROM auth_company WHERE id IN (SELECT company_id FROM auth_company_contact WHERE contact_id = :ct0)', [
		// 	':ct0' => $_SESSION['Contact']['id'],
		// ]);
		// $data['service_list'] = $res;

		$data['service_list_default'] = [];

		// $svc_list = \OpenTHC\Config::get('openthc');
		// var_dump($svc_list);

		// $service_list = $dbc_auth->fetchAll('SELECT id, name FROM auth_service ORDER BY name');
		// $data['service_list'] = $service_list;

		// $data['service_list'] = [];
		// $cfg = \OpenTHC\Config::get('openthc/*');

		$x = \OpenTHC\Config::get('openthc/app/hostname');
		if ($x) {
			$data['service_list_default'][] = [
				'link' => sprintf('https://%s/auth/sso', $x),
				'name' => 'App',
				'hint' => 'Connect to the primary seed-to-sale application for crop and inventory management'
			];
		}

		$x = \OpenTHC\Config::get('openthc/dir/hostname');
		if ($x) {
			$data['service_list_default'][] = [
				'link' => sprintf('https://%s/auth/open?v=sso', $x),
				'name' => 'Directory',
				'hint' => 'Connect to the Directory to update your semi-public contact and company profiles'
			];
		}

		$x = \OpenTHC\Config::get('openthc/lab/hostname');
		if ($x) {
			$data['service_list_default'][] = [
				'link' => sprintf('https://%s/auth/open?v=sso', $x),
				'name' => 'Laboratory Portal',
				'hint' => 'SOmtihng',
			];

		}

		$x = \OpenTHC\Config::get('openthc/pos/hostname');
		if ($x) {
			$data['service_list_default'][] = [
				'link' => sprintf('https://%s/auth/open?v=sso', $x),
				'name' => 'Retail POS',
				'hint' => 'Connect to the Point of Sale to perform front-of-the-house retail operations',
			];
		}

		// $data['service_list'][] = [
		// 	'name' => 'B2B',
		// 	'link' => 'https://b2b.openthc.dev/auth/open?a=oauth',
		// ];

		return $RES->write( $this->render('account/profile.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		\App\CSRF::verify($_POST['CSRF']);

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
