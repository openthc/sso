<?php
/**
 * API Routes
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Module;

class API extends \OpenTHC\Module\Base
{
	/**
	 *
	 */
	function __invoke($a)
	{
		// $a->get('', 'OpenTHC\SSO\Controller\API\Main');

		$a->get('/contact', 'OpenTHC\SSO\Controller\API\Contact\Search')->setName('api/contact/search');

		$a->post('/contact', 'OpenTHC\SSO\Controller\API\Contact\Create')->setName('api/contact/create');
		$a->post('/contact/{id}', 'OpenTHC\SSO\Controller\API\Contact\Update');

		$a->get('/jwt/create', 'OpenTHC\SSO\Controller\API\JWT\Create');
		$a->get('/jwt/verify', 'OpenTHC\SSO\Controller\API\JWT\Verify');
	}

}
