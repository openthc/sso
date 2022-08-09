<?php
/**
 * The base class for integration tests with Browserstack/Selenium + PHPUnit
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

		// $caps['build'] = APP_BUILD;
		$caps['capabilities']['os'] = 'Windows';
		// $caps['capabilities']['os_version'] = 'latest';
		$wb_list = [ 'Chrome', 'Edge', 'Firefox' ];
		$caps['capabilities']['browser'] = $wb_list[ array_rand($wb_list) ];
		// $caps['capabilities']['browser_version'] = 'latest';

		$caps['capabilities']['resolution'] = '1280x1024';

		self::$driver = RemoteWebDriver::create($url, $caps);

	}

	public static function tearDownAfterClass() : void
	{
		self::$driver->quit();
		// if(self::$bs_local) self::$bs_local->stop();
	}
}
