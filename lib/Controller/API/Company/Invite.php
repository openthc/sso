<?php
/**
 * Invite Contact to Company
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\API\Company;

class Invite extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		// Link Company & Contact
		$dbc_auth = $this->_container->DBC_AUTH;

		// $Company = new \OpenTHC\Company($dbc_auth, $ARG['company_id']);
		$Company = $dbc_auth->fetchRow('SELECT id, name FROM auth_company WHERE id = :cy0', [ ':cy0' => $ARG['company_id'] ]);
		if (empty($Company['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Invalid Company [ACI-024]' ]
			], 400);
		}


		// Create a Contact
		$old_post = $_POST;

		$_POST['email'] = $old_post['email'];

		$subC = new \OpenTHC\SSO\Controller\API\Contact\Create($this->_container);
		$resX = $subC->__invoke($REQ, $RES, $ARG);
		switch ($RES->getStatusCode()) {
			case 200:
			case 409: //
				// OK
				break;
			default:
				return $resX;
		}

		$res = $resX->getBody();
		$res->rewind();
		$res = $res->getContents();
		$res = json_decode($res, true);

		$Contact = $res['data'];

		// Auth Company<=>Contact Linkage
		$sql = <<<SQL
		INSERT INTO auth_company_contact (company_id, contact_id)
		VALUES (:cy0, :ct0)
		ON CONFLICT DO NOTHING
		SQL;
		// $sql = 'SELECT company_id FROM auth_company_contact WHERE company_id = :c0 AND contact_id = :c1';
		$arg = [
			':cy0' => $Company['id'],
			':ct0' => $Contact['id'],
		];
		$chk = $dbc_auth->query($sql, $arg);

		// Open Company<=>Contact Linkage
		$dbc_main = $this->_container->DBC_MAIN;
		$sql = <<<SQL
		INSERT INTO company_contact (company_id, contact_id)
		VALUES (:cy0, :ct0)
		ON CONFLICT DO NOTHING
		SQL;
		$chk = $dbc_main->query($sql, $arg);

		// The Auth-Context Token
		// Auth Link
		$act = [];
		$act['id'] = _random_hash();
		$act['meta'] = json_encode([
			'intent' => 'account-invite',
			'service' => $_SERVER['OPENTHC_SERVICE_ID'],
			'contact' => [
				'id' => $C0['id'],
				'name' => $C0['name'],
				'email' => $C0['email'],
				'phone' => $C0['phone'],
			],
			'company' => [
				'id' => $Company['id'],
			],
		]);
		$dbc_auth->insert('auth_context_ticket', $act);

		return $RES->withJSON([
			'data' => [
				'contact' => $Contact['id'],
			],
			'meta' => [],
		]);

	}

}
