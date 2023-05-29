<?php
/**
 * Verify Routes
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Module;

class Verify extends \OpenTHC\Module\Base
{
	/**
	 *
	 */
	function __invoke($app)
	{

		$app->get('', 'OpenTHC\SSO\Controller\Verify\Main')->setName('verify/main');

		$app->get('/email', 'OpenTHC\SSO\Controller\Verify\Email')->setName('verify/email');
		$app->post('/email', 'OpenTHC\SSO\Controller\Verify\Email:post');

		$app->get('/password', 'OpenTHC\SSO\Controller\Account\Password')->setName('verify/password');
		$app->post('/password', 'OpenTHC\SSO\Controller\Account\Password:post');

		$app->get('/location', 'OpenTHC\SSO\Controller\Verify\Location')->setName('verify/location');
		$app->post('/location', 'OpenTHC\SSO\Controller\Verify\Location:post');

		$app->get('/timezone', 'OpenTHC\SSO\Controller\Verify\Timezone')->setName('verify/timezone');
		$app->post('/timezone', 'OpenTHC\SSO\Controller\Verify\Timezone:post');

		$app->get('/phone', 'OpenTHC\SSO\Controller\Verify\Phone')->setName('verify/phone');
		$app->post('/phone', 'OpenTHC\SSO\Controller\Verify\Phone:post');

		$app->get('/company', 'OpenTHC\SSO\Controller\Verify\Company')->setName('verify/company');
		$app->post('/company', 'OpenTHC\SSO\Controller\Verify\Company:post');

		$app->get('/license', 'OpenTHC\SSO\Controller\Verify\License')->setName('verify/license');
		$app->post('/license', 'OpenTHC\SSO\Controller\Verify\License:post');

	}
}
