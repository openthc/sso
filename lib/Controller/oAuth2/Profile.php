<?php
/**
 * Provide a Contact Profile
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\oAuth2;

class Profile extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
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
				'data' => null,
				'meta' => [ 'note' => 'Invalid Request [COP-022]' ]
			], 403);
		}

		// Find Bearer Token
		$sql = 'SELECT id, meta FROM auth_context_ticket WHERE id = :t';
		$arg = [ ':t' => $auth ];
		$tok = $dbc_auth->fetchRow($sql, $arg);
		if (empty($tok)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Invalid Token [COP-030]' ]
			], 400);
		}

		// Find Bearer Token
		$tok['meta'] = json_decode($tok['meta'], true);

		// Auth/Contact
		$sql = 'SELECT id, stat, flag, username FROM auth_contact WHERE id = :c0';
		$arg = [ ':c0' => $tok['meta']['contact_id'] ];
		$Contact = $dbc_auth->fetchRow($sql, $arg);
		if (empty($Contact['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Invalid Token [COP-033]' ],
			], 400);
		}

		$RES = $RES->withAttribute('Contact', $Contact);

		$Profile['Contact']['id'] = $Contact['id'];
		$Profile['Contact']['username'] = $Contact['username'];
		$Profile['Contact']['stat'] = $Contact['stat'];
		$Profile['Contact']['flag'] = $Contact['flag'];

		// Auth/Company
		$sql = 'SELECT id, stat, flag, name, iso3166, tz FROM auth_company WHERE id = ?';
		$arg = [ $tok['meta']['company_id'] ];
		$res = $dbc_auth->fetchRow($sql, $arg);
		if (!empty($res['id'])) {
			$Profile['Company']['id'] = $res['id'];
			$Profile['Company']['stat'] = $res['stat'];
			$Profile['Company']['flag'] = $res['flag'];
			// $Profile['Company']['guid'] = $res['guid'];
			$Profile['Company']['name'] = $res['name'];
			$Profile['Company']['iso3166'] = $res['iso3166'];
			$Profile['Company']['tz'] = $res['tz'];
		}
		$RES = $RES->withAttribute('Company', $Profile['Company']);

		// Auth/Company List
		$sql = <<<SQL
		SELECT id
		FROM auth_company
		WHERE id IN (SELECT company_id FROM auth_company_contact WHERE contact_id = :ct0)
		SQL;
		$arg = [];
		$arg[':ct0'] = $Profile['Contact']['id'];
		$res = $dbc_auth->fetchAll($sql, $arg);
		$Profile['company_list'] = $res;

		// Scope
		$Profile['scope'] = explode(' ', $tok['meta']['scope']);

		// Main/Contact Details
		$Profile['Contact']['fullname'] = ''; // @todo 'cname'
		$Profile['Contact']['email'] = '';
		$Profile['Contact']['phone'] = '';
		if (in_array('contact', $Profile['scope']) || in_array('profile', $Profile['scope'])) {
			$sql = 'SELECT * FROM contact WHERE id = ?';
			$arg = array($Contact['id']);
			$res = $dbc_main->fetchRow($sql, $arg);
			if ( ! empty($res['id'])) {

				$Profile['Contact']['fullname'] = $res['fullname'];

				if ( ! empty($res['email'])) {
					$Profile['Contact']['email'] = $res['email'];
				}
				if ( ! empty($res['phone'])) {
					$Profile['Contact']['phone'] = $res['phone'];
				}

			}
		}

		// Make a JWT that only SSO Can Decipher
		$jwt = new \OpenTHC\JWT([
			'iss' => OPENTHC_SERVICE_ID,
			'exp' => (time() + 3600),
			'sub' => $Profile['Contact']['id'],
			'com' => $Profile['Company']['id'],
			// Config Options
			// service' => 'sso',
			'service-sk' => \OpenTHC\Config::get('openthc/sso/secret'),
		]);
		$Profile['jwt'] = $jwt->__toString();

		return $RES->withJSON($Profile);

	}
}
