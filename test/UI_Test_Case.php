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

	protected static string $stat = 'FAILED';

	public static function setUpBeforeClass() : void
	{
		$caps = [];
		$caps['project'] = 'SSO';
		$caps['build'] = APP_BUILD;
		$caps['name'] = sprintf('PHPUnit %s', strftime('%Y-%m-%d %H:%M:%S'));

		$wb_list = [ 'chrome', 'edge', 'firefox' ]; //, 'safari' ];

		$caps['os'] = 'Windows';
		// $caps['os_version'] = 'latest';
		$caps['browser'] = $wb_list[ array_rand($wb_list) ];
		// $caps['browser_version'] = 'latest';

		// https://www.browserstack.com/docs/automate/selenium/change-screen-resolution#Selenium_4_W3C
		// $caps['resolution'] = '1280x1024';

		// https://www.browserstack.com/docs/automate/selenium/change-device-orientation
		// $caps['deviceOrientation']

		self::$driver = RemoteWebDriver::create(OPENTHC_TEST_WEBDRIVER_URL, $caps);
		self::$driver->manage()->window()->maximize();
	}

	function tearDown() : void
	{
		if (self::$stat != 'FAILED') {
			self::$stat = ($this->hasFailed() ? 'FAILED' : 'PASSED');
		}
	}


	public static function tearDownAfterClass() : void
	{
		$sid = self::$driver->getSessionId();
		// echo "\nDONE SESSION ID: {$sid}\n";

		if ('FAILED' == self::$stat) {

			$url = OPENTHC_TEST_WEBDRIVER_URL;
			$url = parse_url($url);
			// var_dump($url);

			$cfg = [];
			$cfg['username'] = $url['user'];
			$cfg['password'] = $url['pass'];

			$url = sprintf('https://api.browserstack.com/automate/sessions/%s.json', $sid);
			$req = __curl_init($url);
			curl_setopt($req, CURLOPT_USERPWD, sprintf('%s:%s', $cfg['username'], $cfg['password']));
			curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($req, CURLOPT_POSTFIELDS, json_encode([
				'status' => 'failed', // 'completed' is another option?
				'reason' => 'UNKNOWN'
			]));
			curl_setopt($req, CURLOPT_HTTPHEADER, [
				'content-type: application/json'
			]);
			$res = curl_exec($req);
			$res = json_decode($res, true);
			var_dump($res);

			// echo '<pre>';
			// echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			// echo '</pre>';
		}

		sleep(2);

		self::$driver->quit();

	}
}
