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
		// $jwt0 = $_GET['jwt'];
		$jwt0 = $_POST['jwt'];
		// if (!preg_match('/^Bearer jwt:([\w\-\.]+)$/', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
		// 	_exit_json([
		// 		'data' => null,
		// 		'meta' => [ 'note' => 'Invalid Bearer [CAB-013]' ]
		// 	], 400);
		// }
		// $jwt0 = $m[1];

		$chk1 = \OpenTHC\JWT::decode_only($jwt0);
		if (empty($chk1)) {
			return $this->sendFailure('Invalid Token [AJV-030]');
		}

		$key0 = \OpenTHC\Config::get('openthc/sso/secret');
		if (empty($key0)) {
			return $this->sendFailure('Invalid Service [AJV-043]');
		}

		$tok = new \stdClass();
		try {
			$tok = \OpenTHC\JWT::verify($jwt0, $key0);
		} catch (\Exception $e) {
			return $this->sendFailure('Invalid Token [AJV-052]');
		}

		if (empty($tok->sub)) {
			return $this->sendFailure('Invalid Contact [AJV-058]');
		}

		$com = $tok->com ?: $tok->company;
		if (empty($com)) {
			return $this->sendFailure('Invalid Company [AJV-063]');
		}

		$lic = $tok->lic ?: $tok->license;
		// if (empty($lic)) {
		// 	return $this->sendFailure('Invalid License [AJV-068]');
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
