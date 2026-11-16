<?php
/**
 * Update Account
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\SSO\Controller\API\Contact;

class Update extends \OpenTHC\SSO\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		return $RES->withJSON([
			'data' => null,
			'meta' => [ 'note' => 'Not Implemented' ],
		], 501);
	}
}
