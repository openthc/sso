<?php
/**
 * Account Routes
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Module;

class Account extends \OpenTHC\Module\Base
{
	/**
	 *
	 */
	function __invoke($app)
	{
		$app->get('', 'OpenTHC\SSO\Controller\Account\Profile');
		$app->post('', 'OpenTHC\SSO\Controller\Account\Profile:post');

		$app->get('/commit', 'OpenTHC\SSO\Controller\Account\Commit')->setName('account/commit');

		$app->get('/create', 'OpenTHC\SSO\Controller\Account\Create')->setName('account/create');
		$app->post('/create', 'OpenTHC\SSO\Controller\Account\Create:post')->setName('account/create:post');

		$app->get('/password', 'OpenTHC\SSO\Controller\Account\Password');
		$app->post('/password', 'OpenTHC\SSO\Controller\Account\Password:post')->setName('account/password/update');

	}

}
