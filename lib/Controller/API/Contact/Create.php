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
		$Contact = [];
		$Contact['id'] = _ulid();
		$Contact['stat'] = 102;
		$Contact['name'] = trim($_POST['name'] ?: $_POST['email']);
		$Contact['email'] = $_POST['email'];
		$Contact['phone'] = $_POST['phone'];

		if ( ! empty($_POST['email_verify'])) {
			$Contact['flag'] = \OpenTHC\Contact::FLAG_EMAIL_GOOD;
		}

		// $dir = new \OpenTHC\Service\OpenTHC('dir');
		// $res = $dir->get('/api/contact?email=%s', rawurlencode($_POST['email']));
		// switch ($res['code']) {
		// 	case 200:
		// 		break;
		// 	case 404:
		// 		// $dir->post('/api/contact', []);
		// 		break;
		// 	default:
		// 		throw new \Exception('Invalid Response from Directory');
		// }

		// Validate Email
		$Contact['email'] = strtolower(trim($Contact['email']));
		$Contact['email'] = filter_var($Contact['email'], FILTER_VALIDATE_EMAIL);
		// $Contact['email'] = \Edoceo\Radix\Filter::email($Contact['email']); // does DNS lookup
		if (empty($Contact['email'])) {
			return $RES->withJSON([
				'data' => $_POST['email'],
				'meta' => [
					'code' => 'CAC-035',
					'note' => 'Invalid Email'
				]
			], 400);
		}

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
				// $dbc_main->query('UPDATE channel SET stat = :s1 WHERE id = :ch0', [
				// 	':s1' => $Channel['stat'],
				// 	':ch0' => $Channel['id'],
				// ]);
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

		// Auth Contact Check
		$sql = 'SELECT id, username FROM auth_contact WHERE username = :u0';
		$arg = [ ':u0' => $Contact['email'] ];
		$chk = $dbc_auth->fetchRow($sql, $arg);
		if ( ! empty($chk['id'])) {
			return $RES->withJSON([
				'data' => [
					'id' => $chk['id'],
				],
				'meta' => [
					'code' => 'CAC-065',
					'note' => 'Account Exists',
				]
			], 409);
		}

		// Contact (Legacy)
		$sql = 'SELECT id, name, email FROM contact WHERE email = :u0';
		$arg = [ ':u0' => $Contact['email'] ];
		$contact_base = $dbc_main->fetchRow($sql, $arg);
		if ( ! empty($contact_base['id'])) {
			// Trigger Email Verify?
			// return $RES->withJSON([
			// 	'data' => null,
			// 	'meta' => [
			// 		'code' => 'CAC-077',
			// 		'note' => 'Verify Email',
			// 	]
			// ], 409);
		}

		// $Contact_Base = [];
		// if ( ! empty($Contact['id'])) {
		// 		$sql = 'SELECT id FROM contact WHERE id = :c0'; //  OR email = :u0';
		// 		$arg = [ ':c0' => $Contact['id'] ];
		// 		$Contact_Base = $dbc_main->fetchRow($sql, $arg);
		// }
		// if (empty($Contact_Base['id'])) {
		// 		// Try By Email
		// 		$sql = 'SELECT id FROM contact WHERE email = :u0';
		// 		$arg = [ ':u0' => $Contact['email'] ];
		// 		$Contact_Base = $dbc_main->fetchRow($sql, $arg);
		// }
		// if (empty($Contact_Base['id'])) {
		// 		// $Contact_Base = $Contact;
		// 		// $create_contact_base = true;
		// 		// Insert?
		// 		$dbc_main->insert('contact', $Contact);
		// } else {
		// 		$Contact['id'] = $Contact_Base['id'];
		// }

		// Channel -> Contact?
		// Is this Channel Linked to Any Contacts?
		$sql = 'SELECT id, name FROM contact WHERE id IN (SELECT contact_id FROM contact_channel WHERE channel_id = :ch0)';
		$arg = [ ':ch0' => $Channel['id'] ];
		$contact_base_list = $dbc_main->fetchAll($sql, $arg);
		switch (count($contact_base_list)) {
			case 0:
				// Will Create
				break;
			case 1:
				// Will Update / Invite
				// does this match the $contact_base ?
				break;
			default:
				// Not sure what to do here
				// Does one Match the Contact Base?
				break;
		}

		// Return
		$ret_data = [];

		$dbc_auth->query('BEGIN');
		$dbc_main->query('BEGIN');

		// Should Always Work
		$dbc_auth->insert('auth_contact', array(
			'id' => $Contact['id'],
			// 'flag' => $Contact['flag'],
			// 'stat' => $Contact['stat'],
			'username' => $Contact['email'],
			'password' => '',
		));

		// Commit Channel
		$dbc_main->insert('contact', $Contact);

		// Channel Linkage
		$dbc_main->query('INSERT INTO contact_channel (contact_id, channel_id) VALUES (:ct0, :ch0) ON CONFLICT DO NOTHING', [
			':ct0' => $Contact['id'],
			':ch0' => $Channel['id'],
		]);

		$dbc_auth->query('COMMIT');
		$dbc_main->query('COMMIT');

		$ret_data['id'] = $Contact['id'];

		return $RES->withJSON([
			'data' => $ret_data,
			'meta' => [],
		], 201);

	}

}
