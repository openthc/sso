<?php
/**
 * Notify Controller unit test
 * Show the user a notification, and remember where they were supposed to go.
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller;

use OpenTHC\SSO\Test\Base;

class Controller_Notify_Test extends Base
{
	function test_make_url()
	{
		$this->assertEquals('/notify?r=%2Fprofile', Notify::make_url('/profile'));
		$this->assertEquals('/notify/test?r=%2Fprofile', Notify::make_url('/profile', 'test'));
	}

	function test_notify()
	{
		// Mock slim environment
		$container = new \Slim\Container;

		$notify = new \OpenTHC\SSO\Controller\Notify($container);

		$req = \Slim\Http\Request::createFromEnvironment(\Slim\Http\Environment::mock());
		$res = new \Slim\Http\Response();
		$x = $notify($req, $res, []);

		// Our mock implementation works
		$this->assertEquals(302, $x->getStatusCode());

		// Test with ID
		$yaml = <<<YAML
		title: Test Notification
		head: Test Notification
		body: This is a test notification.
		YAML;
		file_put_contents(sprintf('%s/etc/notify/test-2024-347.yaml', APP_ROOT), $yaml);
		$x = $notify($req, $res, [ 'r' => '/the-next-url']);
		$this->assertEquals(200, $x->getStatusCode());
		$this->assertStringContainsString('<div class="card-header"><h2>Test Notification</h2></div>', (string)$res->getBody());

		// Test POST
		$_POST = [
			'notify_id' => 'test-2024-347',
			'next_url' => '/the-next-url',
		];
		$x = $notify->post($req, $res, []);
		$this->assertEquals(302, $x->getStatusCode());
		$this->assertEquals('/the-next-url', $x->getHeaderLine('Location'));

		unlink(sprintf('%s/etc/notify/test-2024-347.yaml', APP_ROOT));

	}
}
