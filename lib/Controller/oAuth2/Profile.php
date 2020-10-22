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
			'scope' => [],
			'Contact' => [],
			'Company' => [],
		);

		$auth = preg_match('/^Bearer ([\w\-]+)$/', $_SERVER['HTTP_AUTHORIZATION'], $m) ? $m[1] : null;
		if (empty($auth)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Request [COP#022]' ]
			], 403);
		}

		// Find Bearer Token
		$sql = 'SELECT id, meta FROM auth_context_ticket WHERE id = ?';
		$arg = array($auth);
		$tok = $dbc->fetchRow($sql, $arg);
		if (empty($tok)) {
			return $RES->withJSON([
				'meta' => ['detail' => 'Invalid Token [COP#030]' ]
			], 400);
		}

		// Find Bearer Token
		$tok['meta'] = json_decode($tok['meta'], true);

		// Contact/Auth
		$sql = 'SELECT * FROM auth_contact WHERE id = ?';
		$arg = array($tok['meta']['contact_id']);
		$Contact = $dbc->fetchRow($sql, $arg);
		if (empty($Contact['id'])) {
			return $RES->withJSON([
				'meta' => ['detail' => 'Invalid Token [COP#033]' ],
			], 400);
		}

		$RES = $RES->withAttribute('Contact', $Contact);

		$Profile['scope'] = explode(' ', $Contact['scope_permit']);

		$Profile['Contact']['id'] = $Contact['id'];
		$Profile['Contact']['fullname'] = $Contact['fullname'];
		$Profile['Contact']['username'] = $Contact['username'];

		// Contact
		$sql = 'SELECT * FROM contact WHERE id = ?';
		$arg = array($Contact['contact_id']);
		$res = $dbc->fetchRow($sql, $arg);
		if (!empty($res['email'])) {
			$Profile['Contact']['email'] = true;
		}
		if (!empty($res['phone'])) {
			$Profile['Contact']['phone'] = true;
		}

		// Company
		$sql = 'SELECT id,guid,name,type FROM company WHERE id = ?';
		$arg = array($Contact['company_id']);
		$res = $dbc->fetchRow($sql, $arg);
		if (!empty($res['id'])) {
			$Profile['Company']['id'] = $res['id'];
			$Profile['Company']['ulid'] = $res['id']; // @deprecated
			$Profile['Company']['guid'] = $res['guid'];
			$Profile['Company']['name'] = $res['name'];
			$Profile['Company']['type'] = $res['type'];
		}

		return $RES->withJSON($Profile);

	}
}
