<?php
/**
 * Response Factory
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\HTTP\Response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Factory implements ResponseFactoryInterface
{
	public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface
	{
		return new \OpenTHC\SSO\HTTP\Response($code, [], null, '1.1', $reasonPhrase);
	}
}
