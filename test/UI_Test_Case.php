<?php
/**
 * The base class for integration tests with Browserstack/Selenium + PHPUnit
 * https://www.browserstack.com/automate/phpunit
 * https://www.browserstack.com/automate/php
 * https://github.com/browserstack/phpunit-browserstack
 * https://www.browserstack.com/docs?product=automate
 */

namespace Test;

use \Facebook\WebDriver\Remote\RemoteWebDriver;

class UI_Test_Case extends \Test\Base_Case
{
	protected static $driver;
	// protected static $bs_local;

	public static function setUpBeforeClass() : void
	{
		$config_file = sprintf('%s/etc/browserstack.conf.json', APP_ROOT);
		$config = json_decode(file_get_contents($config_file), true);

		$task_id = getenv('TASK_ID') ? getenv('TASK_ID') : 0;

		$url = sprintf('https://%s:%s@%s/wd/hub', $config['user'], $config['key'], $config['server']);
		$caps = $config['environments'][$task_id];

		foreach ($config["capabilities"] as $key => $value) {
			if(!array_key_exists($key, $caps))
				$caps[$key] = $value;
		}

		$caps['build'] = APP_BUILD;
		$caps['name'] = sprintf('PHPUnit Test Case: %s', strftime('%Y-%m-%d %H:%M:%S'));

		self::$driver = RemoteWebDriver::create($url, $caps);
	}

	public static function tearDownAfterClass() : void
	{
		self::$driver->quit();
		// if(self::$bs_local) self::$bs_local->stop();
	}
}
