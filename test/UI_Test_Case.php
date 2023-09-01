<?php
/**
 * The base class for integration tests with Browserstack/Selenium + PHPUnit
 *
 * SPDX-License-Identifier: MIT
 *
 * https://www.browserstack.com/automate/phpunit
 * https://www.browserstack.com/automate/php
 * https://github.com/browserstack/phpunit-browserstack
 * https://www.browserstack.com/docs?product=automate
 */

namespace OpenTHC\SSO\Test;

use \Facebook\WebDriver\Remote\RemoteWebDriver;

class UI_Test_Case extends \OpenTHC\SSO\Test\Base_Case
{
	protected static $driver;
	// protected static $bs_local;

	public static function setUpBeforeClass() : void
	{
		$url = getenv('OPENTHC_TEST_WEBDRIVER_URL');

		$caps['capabilities'] = [];
		$caps['capabilities']['project'] = 'SSO';
		$caps['capabilities']['build'] = '420.21.235';
		$caps['capabilities']['name'] = sprintf('PHPUnit Test Case: %s', strftime('%Y-%m-%d %H:%M:%S'));

		$os_list = [ 'os x', 'windows' ];
		$wb_list = [ 'chrome', 'edge', 'firefox' ]; //, 'safari' ];

		// $caps['build'] = APP_BUILD;
		$caps['capabilities']['os'] = 'Windows';
		// $caps['capabilities']['os_version'] = 'latest';
		$caps['capabilities']['browser'] = $wb_list[ array_rand($wb_list) ];
		// $caps['capabilities']['browser_version'] = 'latest';

		$caps['capabilities']['resolution'] = '1280x1024';

		// https://www.browserstack.com/docs/automate/selenium/change-screen-resolution#Selenium_4_W3C
		// $caps['resolution'] = '1280x1024';

		// https://www.browserstack.com/docs/automate/selenium/change-device-orientation
		// $caps['deviceOrientation']

		$caps['bstack:options'] = [
			'os' => $os_list[ array_rand($os_list) ],
			// 'osVersion' => '',
			'projectName' => 'SSO',
			'buildName' => sprintf('v%s', APP_BUILD),
			'sessionName' => sprintf('TEST RUN %d', getmypid())
		];

		self::$driver = RemoteWebDriver::create($url, $caps);
		self::$driver->manage()->window()->maximize();
	}

	public static function tearDownAfterClass() : void
	{
		self::$driver->quit();
		// if(self::$bs_local) self::$bs_local->stop();
	}
}
