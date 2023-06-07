<?php
/**
 * Account Search
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\API\Contact;

class Search extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$q = trim($_GET['q']);

		$Contact = [];
		if (preg_match('/\w+@\w+/', $q)) {
			$Contact['email'] = strtolower(trim($q));
		}

		// Validate Email
		$Contact['email'] = filter_var($Contact['email'], FILTER_VALIDATE_EMAIL);
		if (empty($Contact['email'])) {
			return $RES->withJSON([
				'data' => $q,
				'meta' => [
					'code' => 'CAS-026',
					'note' => 'Invalid Email'
				]
			], 400);
		}

		// $e3 = \Edoceo\Radix\Filter::email($e1);
		// if (empty($e)) {
		// 	return $RES->withRedirect('/account/create?e=CAC-035');
		// }

		// $Contact['name'] = trim($_POST['name']);
		$Contact['username'] = $Contact['email'];

		// Do Database Things
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// Channel
		$sql = 'SELECT id, stat, type, data FROM channel WHERE type = :t0 AND data = :e0';
		$arg = [
			':e0' => $Contact['email'],
			':t0' => 'email',
		];
		$Channel = $dbc_main->fetchRow($sql, $arg);
		if (empty($Channel['id'])) {
			$Channel = [];
			$Channel['id'] = _ulid();
			$Channel['data'] = $Contact['email'];
			$Channel['type'] = 'email';
			$Channel['stat'] = 100;
			$dbc_main->insert('channel', $Channel);
			syslog(LOG_NOTICE, "channel/create {$Channel['id']} {$Contact['email']}");
		}
		switch ($Channel['stat']) {
			case 400:
			case 404:
			case 410:
			case 666:
				return $RES->withJSON([
					'data' => null,
					'meta' => [
						'code' => 'ACC-065',
						'note' => 'Invalid Email',
					]
				], 400);
		}


		// Main Contact Check (Legacy)
		$sql = 'SELECT id, email FROM contact WHERE email = :u0';
		$arg = [ ':u0' => $Contact['email'] ];
		$chk = $dbc_main->fetchRow($sql, $arg);
		if ( ! empty($chk['id'])) {
			// In Main
			$RES = $RES->withAttribute('Contact_Base', $chk);
			// Trigger Email Verify?
			// return $RES->withJSON([
			// 	'data' => null,
			// 	'meta' => [
			// 		'code' => 'CAC-077',
			// 		'note' => 'Verify Email',
			// 	]
			// ], 409);
		}


		// Auth Contact Check
		$sql = 'SELECT id, flag, stat, username FROM auth_contact WHERE username = :u0';
		$arg = [ ':u0' => $Contact['email'] ];
		$chk = $dbc_auth->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			return $RES->withJSON([
				'data' => $Contact,
				'meta' => [
					'code' => 'CAC-085',
					'note' => 'Not Found',
				]
			], 404);
		}

		$Contact['id'] = $chk['id'];
		$Contact['flag'] = $chk['flag'];
		$Contact['stat'] = $chk['stat'];

		return $RES->withJSON([
			'data' => $Contact,
			'meta' => []
		], 200);

	}

}
