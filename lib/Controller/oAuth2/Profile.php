<?php
/**
 * Provide a Contact Profile
 */

namespace App\Controller\oAuth2;

class Profile extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DB;

		$Profile = array(
			'Contact' => [],
			'Company' => [],
		);

		$auth = preg_match('/^Bearer (\w+)$/', $_SERVER['HTTP_AUTHORIZATION'], $m) ? $m[1] : null;
		if (empty($auth)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Request [COP#022]' ]
			], 403);
		}

		// Find Bearer Token
		$sql = 'SELECT id, meta FROM auth_context_secret WHERE code = ?';
		$arg = array(sprintf('oauth-token:%s', $auth));
		$tok = $dbc->fetchRow($sql, $arg);
		if (empty($tok)) {
			return $RES->withJSON([
				'meta' => ['detail' => 'Invalid Token [COP#030]' ]
			], 400);
		}

		// Find Bearer Token
		$tok['meta'] = json_decode($tok['meta'], true);

		$Profile['Token'] = $tok['meta'];
		$Profile['scope'] = $tok['meta']['scope'];

		// Contact/Auth
		$sql = 'SELECT * FROM auth_contact WHERE id = ?';
		$arg = array($tok['meta']['contact_id']);
		$AppUser = $dbc->fetchRow($sql, $arg);
		// echo '<pre>';
		// var_dump($AppUser);
		if (empty($AppUser['id'])) {
			return $RES->withJSON([
				'meta' => ['detail' => 'Invalid Token [COP#033]' ],
			], 400);
		}

		$Profile['Contact']['id'] = $AppUser['id'];
		$Profile['Contact']['id_int8'] = $AppUser['id'];
		$Profile['Contact']['id_ulid'] = $AppUser['contact_id'];
		$Profile['Contact']['fullname'] = $AppUser['fullname'];
		$Profile['Contact']['username'] = $AppUser['username'];

		// Contact
		$sql = 'SELECT * FROM contact WHERE id = ?';
		$arg = array($AppUser['contact_id']);
		$res = $dbc->fetchRow($sql, $arg);
		if (!empty($res['email'])) {
			$Profile['Contact']['email'] = true;
		}
		if (!empty($res['phone'])) {
			$Profile['Contact']['phone'] = true;
		}

		// Company
		$sql = 'SELECT * FROM company WHERE id = ?';
		$arg = array($AppUser['company_id']);
		$res = $dbc->fetchRow($sql, $arg);
		if (!empty($res['id'])) {
			$Profile['Company']['id'] = $res['id'];
			$Profile['Company']['ulid'] = $res['id'];
			$Profile['Company']['guid'] = $res['guid'];
			$Profile['Company']['name'] = $res['name'];
			$Profile['Company']['type'] = $res['type'];
		}

		return $RES->withJSON($Profile);

	}
}
