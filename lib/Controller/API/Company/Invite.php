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
		$dbc_auth = $this->dic->get('DBC_AUTH');

		// $Company = new \OpenTHC\Company($dbc_auth, $ARG['company_id']);
		$Company = $dbc_auth->fetchRow('SELECT id, name FROM auth_company WHERE id = :cy0', [ ':cy0' => $ARG['company_id'] ]);
		if (empty($Company['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Invalid Company [ACI-024]' ]
			], 400);
		}


		// Create a Contact
		$_POST['name'] = $_POST['contact']['name'];
		$_POST['email'] = $_POST['contact']['email'];
		$_POST['phone'] = $_POST['contact']['phone'];
		// unset($_POST['contact']);

		$subC = new \OpenTHC\SSO\Controller\API\Contact\Create($this->_container);
		$resX = $subC->__invoke($REQ, $RES, $ARG);
		// switch ($resX->getStatusCode()) {
		// 	case 200:
		// 	case 409: //
		// 		// OK
		// 		break;
		// 	default:
		// 		return $resX;
		// }

		$res = $resX->getBody();
		$res->rewind();
		$res = $res->getContents();

		$res = json_decode($res, true);

		$Contact = $res['data'];
		// @todo Bug on Response Body, see the Contact\Create controller
		if ( ! empty($Contact['contact'])) {
			$Contact = $Contact['contact'];
		}

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
		$dbc_main = $this->dic->get('DBC_MAIN');
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
				'id' => $Contact['id'],
				'name' => $Contact['name'],
				'email' => $Contact['email'],
				'phone' => $Contact['phone'],
			],
			'company' => [
				'id' => $Company['id'],
			],
		]);
		$dbc_auth->insert('auth_context_ticket', $act);

		$RES = $RES->withAttribute('Company', $Company);
		$RES = $RES->withAttribute('Contact', $Contact);
		$RES = $RES->withAttribute('Auth_Context_Ticket', $act);

		return $RES->withJSON([
			'data' => [
				'contact' => $Contact,
			],
			'meta' => [],
		]);

	}

}
