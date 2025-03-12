<?php
/**
 * View your Own Account
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Account;

use OpenTHC\CSRF;
use OpenTHC\SSO\Auth_Contact;

class Profile extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page'] = [ 'title' => 'Profile' ];
		if (empty($_SESSION['Contact']['id'])) {
			_exit_html_fail('<h1>Invalid Session [CAP-017]</h1><p>Please <a href="/auth/shut">close the session</a> and try again.</p>', 403);
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
		$sql = <<<SQL
		SELECT id
			, name AS auth_name
			, stat AS auth_stat
			, guid
			, iso3166
			, tz
			, cre
			, length(dsn) AS dsn
		FROM auth_company
		WHERE id IN (
			SELECT company_id
			FROM auth_company_contact
			WHERE contact_id = :ct0
		)
		ORDER BY id
		SQL;
		$data['company_list'] = $dbc_auth->fetchAll($sql, [
			':ct0' => $_SESSION['Contact']['id'],
		]);

		foreach ($data['company_list'] as $idx => $Company0) {
			$sql = <<<SQL
			SELECT id
				, name AS main_name
				, stat AS main_stat
				, flag AS main_flag
				-- , guid
				-- , cre AS main_cre
				-- , iso3166
				-- , tz
			FROM company
			WHERE id = :c0
			SQL;
			$Company1 = $dbc_main->fetchRow($sql, [ ':c0' => $Company0['id'] ]);
			// Somethign
			if ( ! empty($Company1['id'])) {
				$data['company_list'][$idx] = array_merge($Company0, $Company1);
			}
		}

		// $company_good = false;
		// foreach ($data['company_list'] as $rec) {
		// 	if (($rec['stat'] == 200) && ( ! empty($rec['dsn']))) {
		// 		$company_good = true;
		// 	}
		// }

		// Active Service List
		// $res = $dbc_auth->fetchAll('SELECT id, name FROM auth_company WHERE id IN (SELECT company_id FROM auth_company_contact WHERE contact_id = :ct0)', [
		// 	':ct0' => $_SESSION['Contact']['id'],
		// ]);
		// $data['service_list'] = $res;

		$data['service_list'] = [];

		$x = \OpenTHC\Config::get('openthc/dir/origin');
		if ($x) {
			$data['service_list'][] = [
				'link' => '/service/connect/dir',
				'name' => 'Directory',
				'hint' => 'Connect to the Directory to update your semi-public contact and company profiles'
			];
		}

		$x = \OpenTHC\Config::get('openthc/app/origin');
		if ($x) {
			$data['service_list'][] = [
				'link' => '/service/connect/app', // sprintf('%s/auth/open?a=sso', $x),
				'name' => 'App',
				'hint' => 'Connect to the primary seed-to-sale application for crop and inventory management'
			];
		}

		$x = \OpenTHC\Config::get('openthc/pos/origin');
		if ($x) {
			$data['service_list'][] = [
				'link' => '/service/connect/pos',
				'name' => 'Retail POS',
				'hint' => 'Connect to the Point of Sale to perform front-of-the-house retail operations',
			];
		}

		$x = \OpenTHC\Config::get('openthc/b2b/origin');
		if ($x) {
			$data['service_list'][] = [
				'link' => '/service/connect/b2b',
				'name' => 'B2B Marketplace',
				'hint' => 'Connect to the B2B Marketplace to connect with vendors and suppliers',
			];
		}

		return $RES->write( $this->render('account/profile.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		CSRF::verify($_POST['CSRF']);

		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		switch ($_POST['a']) {
			case 'contact-email-update':
				return $this->email_update($RES);
				break;
			case 'contact-name-save':
				$n = trim($_POST['contact-name']);
				$dbc_main->query('UPDATE contact SET name = :n1 WHERE id = :c0', [
					':c0' => $_SESSION['Contact']['id'],
					':n1' => $n,
				]);
				$_SESSION['Contact']['name'] = $n;
				break;
			case 'contact-password-update':
				$act = [
					'intent' => 'password-update',
					'contact' => [
						'id' => $_SESSION['Contact']['id']
					]
				];
				$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act);
				return $RES->withRedirect(sprintf('/account/password?_=%s', $tok));
				break;
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

	function email_update($RES)
	{
		$e = strtolower(trim($_POST['contact-email']));
		// $e = filter_var();
		$e = \Edoceo\Radix\Filter::email($e);
		if (empty($e)) {
			_exit_html_fail('<h1>Invalid Email [CAP-169]</h1>', 400);
		}

		$dbc_auth = $this->_container->DBC_AUTH;

		// Find in Channel?
		// $Ch0 = \Channel::findBy();

		$CT0 = new Auth_Contact($dbc_auth, $_SESSION['Contact']['id']);
		$CT0['username'] = $e;
		$CT0->save('Contact/Update by User');

		return $RES->withRedirect('/profile');
	}
}
