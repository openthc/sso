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

		$a->post('/company/{company_id}/invite', 'OpenTHC\SSO\Controller\API\Company\Invite')->setName('api/company/invite');

		$a->get('/contact', 'OpenTHC\SSO\Controller\API\Contact\Search')->setName('api/contact/search');

		$a->post('/contact', 'OpenTHC\SSO\Controller\API\Contact\Create')->setName('api/contact/create');
		$a->post('/contact/{id}', 'OpenTHC\SSO\Controller\API\Contact\Update');

		$a->post('/jwt/create', 'OpenTHC\SSO\Controller\API\JWT\Create');
		$a->post('/jwt/verify', 'OpenTHC\SSO\Controller\API\JWT\Verify');
	}

}
