<?php
/**
 * API Base Controller
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\API;

class Base extends \OpenTHC\Controller\Base
{
	/**
	 * Sends an Failure Response
	 */
	protected function sendFailure($note, $code=400)
	{
		// $type_want
		$RES = new \OpenTHC\HTTP\Response();
		$RES = $RES->withBody(new \Slim\Http\RequestBody());
		$RES = $RES->withJSON([
			'data' => null,
			'meta' => [ 'note' => $note ]
		], $code);
		return $RES;
	}

}
