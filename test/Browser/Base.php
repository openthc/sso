<?php
/**
 * The base class for integration tests with PHPUnit+Selenium/WebDriver
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\Browser;

class Base extends \OpenTHC\Test\BaseBrowser
{
	public static function setUpBeforeClass() : void
	{
		self::$cfg = [];
		self::$cfg['project'] = 'SSO';
		self::$cfg['build'] = APP_BUILD;
		self::$cfg['name'] = sprintf('%s v%s @ %s', self::$cfg['project'], self::$cfg['build'], strftime('%Y-%m-%d %H:%M'));

		parent::setUpBeforeClass();

		// Visit site before setting cookie for easy domain registration in the cookie
		self::$wd->get($_ENV['OPENTHC_TEST_ORIGIN']);
		self::$wd->manage()->addCookie([
			'name' => 'openthc-test',
			'value' => \OpenTHC\Config::get('openthc/sso/test/sk'),
			'Secure' => true,
			'HttpOnly' => true,
		]);

	}

	public static function _dbc($dsn)
	{
		switch ($dsn) {
		case 'auth':
			$cfg = \OpenTHC\Config::get('database/auth');
			break;

		case 'main':
		default:
			$cfg = \OpenTHC\Config::get('database/main');
			break;
		}
		$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
		return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
	}
}
