<?php
/**
 * Verify JWT Token and Provide Inflated Details
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\API\JWT;

class Verify extends \OpenTHC\SSO\Controller\API\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$jwt0 = $_GET['jwt'];
		// if (!preg_match('/^Bearer jwt:([\w\-\.]+)$/', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
		// 	_exit_json([
		// 		'data' => null,
		// 		'meta' => [ 'note' => 'Invalid Bearer [CAB-013]' ]
		// 	], 400);
		// }
		// $jwt0 = $m[1];

		$chk1 = \OpenTHC\JWT::decode_only($jwt0);
		if (empty($chk1)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Invalid Token [AJV-030] ']
			], 400);
		}

		$rdb = \OpenTHC\Service\Redis::factory();
		$key = $rdb->get(sprintf('/service/%s/sk', $chk1->body->iss));
		if (empty($key)) {
			$dbc = $this->_container->DBC_AUTH;
			$key = $dbc->fetchOne('SELECT hash FROM auth_service WHERE id = :s0', [ ':s0' => $chk1->body->iss ]);
			$rdb->set(sprintf('/service/%s/sk', $chk1->body->iss), $key, [ 'ex' => 900 ]);
		}
		if (empty($key)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Invalid Service [AJV-043] ']
			], 400);
		}

		$tok = new \stdClass();
		try {
			$tok = \OpenTHC\JWT::verify($jwt0, $key);
		} catch (\Exception $e) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'note' => 'Invalid Service [AJV-052] ']
			], 400);
		}

		if (empty($tok->sub)) {
			return $RES->withJSON(['meta' => [ 'note' => 'Invalid Contact [AJV-058]' ]], 400);
		}

		$com = $tok->com ?: $tok->company;
		if (empty($com)) {
			return $RES->withJSON(['meta' => [ 'note' => 'Invalid Company [AJV-063]' ]], 400);
		}

		$lic = $tok->lic ?: $tok->license;
		// if (empty($com)) {
		// 	return $RES->withJSON(['meta' => [ 'note' => 'Invalid License [AJV-068]' ]], 400);
		// }

		// Load the Contact, Company, License which has authorized the service
		// $chk_con = $rdb->get(sprintf('/service/%s/contact/%s', $chk->body->iss, $tok->sub));
		// $chk_com = $rdb->get(sprintf('/service/%s/company/%s', $chk->body->iss, $tok->com));
		// $chk_lic = $rdb->get(sprintf('/service/%s/license/%s', $chk->body->iss, $tok->lic));

		$ret = [
			'data' => [
				'Service' => [
					$tok->iss,
				],
				'Contact' => [
					'id' => $tok->sub,
				],
				'Company' => [
					'id' => $com,
				],
				'License' => [
					'id' => $lic,
				]
			],
			'meta' => []
		];

		return $RES->withJSON($ret);

	}

}
