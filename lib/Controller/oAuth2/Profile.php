<?php
/**
 * Provide a Contact Profile
 */

namespace App\Controller\oAuth2;

class Profile extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		$Profile = array(
			'scope' => [],
			'Contact' => [],
			'Company' => [],
			'License' => [],
		);

		// Method 1
		$auth = $_GET['access_token'];

		// Method 2
		if (empty($auth)) {
			$auth = preg_match('/^Bearer ([\w\-]+)$/', $_SERVER['HTTP_AUTHORIZATION'], $m) ? $m[1] : null;
		}

		if (empty($auth)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Request [COP-022]' ]
			], 403);
		}

		// Find Bearer Token
		$sql = 'SELECT id, meta FROM auth_context_ticket WHERE id = :t';
		$arg = [ ':t' => $auth ];
		$tok = $dbc_auth->fetchRow($sql, $arg);
		if (empty($tok)) {
			return $RES->withJSON([
				'meta' => ['detail' => 'Invalid Token [COP#030]' ]
			], 400);
		}

		// Find Bearer Token
		$tok['meta'] = json_decode($tok['meta'], true);

		// Auth/Contact
		$sql = 'SELECT id, username FROM auth_contact WHERE id = :c0';
		$arg = [ ':c0' => $tok['meta']['contact_id'] ];
		$Contact = $dbc_auth->fetchRow($sql, $arg);
		if (empty($Contact['id'])) {
			return $RES->withJSON([
				'meta' => ['detail' => 'Invalid Token [COP#033]' ],
			], 400);
		}

		$RES = $RES->withAttribute('Contact', $Contact);

		$Profile['Contact']['id'] = $Contact['id'];
		$Profile['Contact']['username'] = $Contact['username'];

		// Auth/Company
		$sql = 'SELECT id, name FROM auth_company WHERE id = ?';
		$arg = [ $tok['meta']['company_id'] ];
		$res = $dbc_auth->fetchRow($sql, $arg);
		if (!empty($res['id'])) {
			$Profile['Company']['id'] = $res['id'];
			$Profile['Company']['name'] = $res['name'];
		}
		$RES = $RES->withAttribute('Company', $Company);

		// Scope
		$Profile['scope'] = explode(' ', $tok['meta']['scope']);

		// Main/Contact
		$sql = 'SELECT * FROM contact WHERE id = ?';
		$arg = array($Contact['id']);
		$res = $dbc_main->fetchRow($sql, $arg);
		if (!empty($res['id'])) {

			$Profile['Contact']['fullname'] = $res['fullname'];

			if (!empty($res['email'])) {
				$Profile['Contact']['email'] = true;
			}
			if (!empty($res['phone'])) {
				$Profile['Contact']['phone'] = true;
			}

		}

		return $RES->withJSON($Profile);

	}
}
