<?php
/**
 * Create JWT Token
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\API\JWT;

class Create extends \OpenTHC\SSO\Controller\API\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		// Needs an authenticated session to issue a JWT?

		$company_id = $_POST['company']['id'];
		$contact_id = $_POST['contact']['id'];
		$password = $_POST['contact']['password'];

		// Verify it All
		$dbc = $this->_container->DBC_AUTH;

		// $CP0 = new \OpenTHC\Company($dbc, $company);
		$CP0 = $dbc->fetchRow('SELECT id FROM auth_company WHERE id = :cp0', [ ':cp0' => $company_id ]);
		$CT0 = $dbc->fetchRow('SELECT id, password FROM auth_contact WHERE id = :ct0', [ ':ct0' => $contact_id ]);
		// new \OpenTHC\SSO\Auth_Contact($dbc, $contact_id);

		if (empty($CP0['id']) || empty($CT0['id'])) {
			return $this->sendFailure($RES, 'Invalid Company or Contact [AJC-031]');
		}

		if ( ! password_verify($password, $CT0['password'])) {
			return $this->sendFailure($RES, 'Invalid Company or Contact [AJC-036]');
		}

		// Create
		$ttl = intval($_POST['ttl']) ?: 3600;

		$jwt = new \OpenTHC\JWT([
			'iss' => OPENTHC_SERVICE_ID,
			'sub' => $CT0['id'],
			'com' => $CP0['id'],
			'exp' => (time() + $ttl),
			// 'license' => $_SESSION['License']['id'],
		]);

		return $RES->withJSON([
			'data' => $jwt->__toString(),
			'meta' => [],
		], 201);

	}

}
