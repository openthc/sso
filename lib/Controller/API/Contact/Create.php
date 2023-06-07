<?php
/**
 * Create Account
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\API\Contact;

class Create extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		// Validate Email
		$e = strtolower(trim($_POST['email']));
		$e = filter_var($e, FILTER_VALIDATE_EMAIL);
		if (empty($e)) {
			return $RES->withJSON([
				'data' => $_POST['email'],
				'meta' => [
					'code' => 'CAC-035',
					'note' => 'Invalid Email'
				]
			], 400);
		}

		// $e3 = \Edoceo\Radix\Filter::email($e1);
		// if (empty($e)) {
		// 	return $RES->withRedirect('/account/create?e=CAC-035');
		// }
		$Contact = [];
		$Contact['id'] = _ulid();
		$Contact['flag'] = \OpenTHC\Contact::FLAG_EMAIL_GOOD;
		$Contact['stat'] = 100;
		$Contact['name'] = trim($_POST['name']);
		$Contact['email'] = $e;

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
		}
		switch ($Channel['stat']) {
			case 100:
				$Channel['stat'] = 200;
				$dbc_main->query('UPDATE channel SET stat = :s1 WHERE id = :ch0', [
					':s1' => $Channel['stat'],
					':ch0' => $Channel['id'],
				]);
				break;
			case 200:
				// OK
				break;
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
			default:
				return $RES->withJSON([
					'data' => $Channel,
					'meta' => [
						'code' => 'ACC-085',
						'note' => 'Invalid Channel',
					]
				], 500);
		}

		$dbc_auth->query('BEGIN');
		$dbc_main->query('BEGIN');

		// Auth Contact Check
		$sql = 'SELECT id, username FROM auth_contact WHERE username = :u0';
		$arg = [ ':u0' => $Contact['email'] ];
		$chk = $dbc_auth->fetchRow($sql, $arg);
		if ( ! empty($chk['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [
					'code' => 'CAC-065',
					'note' => 'Account Exists',
				]
			], 409);
		}

		// Contact (Legacy)
		$sql = 'SELECT id, email FROM contact WHERE email = :u0';
		$arg = [ ':u0' => $Contact['email'] ];
		$chk = $dbc_main->fetchRow($sql, $arg);
		if ( ! empty($chk['id'])) {
			// Trigger Email Verify?
			return $RES->withJSON([
				'data' => null,
				'meta' => [
					'code' => 'CAC-077',
					'note' => 'Verify Email',
				]
			], 409);
		}

		// Return
		$ret_data = [];

		$dbc_auth->query('BEGIN');
		$dbc_main->query('BEGIN');

		// Should Always Work
		$dbc_auth->insert('auth_contact', array(
			'id' => $Contact['id'],
			'flag' => $Contact['flag'],
			'stat' => $Contact['stat'],
			'username' => $Contact['email'],
			'password' => '',
		));

		// Commit Channel
		$dbc_main->insert('contact', $Contact);

		// Channel Linkage
		$dbc_main->query('INSERT INTO contact_channel (contact_id, channel_id) VALUES (:ct0, :cl0)', [
			':ct0' => $Contact['id'],
			':cl0' => $Channel['id'],
		]);

		$dbc_auth->query('COMMIT');
		$dbc_main->query('COMMIT');

		$ret_data['id'] = $Contact['id'];

		// Pass Token in ARGS if TEST
		if ('TEST' == getenv('OPENTHC_TEST')) {
			$ret_data['t'] = $act['id'];
		}

		return $RES->withJSON([
			'data' => $ret_data,
			'meta' => [],
		], 201);

	}
}
