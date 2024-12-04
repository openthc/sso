<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\A_Core;

class B_System_Test extends \OpenTHC\SSO\Test\Base
{
	function test_extensions()
	{
		// $x = function_exists('geoip_record_by_name');
		// $this->assertEmpty($x, '');
		$this->assertTrue(extension_loaded('pdo'));
		$this->assertTrue(extension_loaded('pdo_pgsql'));
		$this->assertTrue(extension_loaded('pdo_sqlite'));

	}

	/**
	 *
	 */
	function test_database()
	{
		// Auth Database
		$cfg = \OpenTHC\Config::get('database/auth');
		$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
		try {
			$dbc = new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
			$this->assertTrue(true);
		} catch (\Exception $e) {
			$this->assertTrue(false, 'Cannot Connect to Auth Database');
		}

		// Main Database
		$cfg = \OpenTHC\Config::get('database/main');
		$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
		try {
			$dbc = new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
			$this->assertTrue(true);
		} catch (\Exception $e) {
			$this->assertTrue(false, 'Cannot Connect to Main Database');
		}

	}


	/**
	 *
	 */
	function test_iso()
	{
		$f = '/usr/share/iso-codes/json/iso_3166-1.json';
		$this->assertTrue(is_file($f));

		$f = '/usr/share/iso-codes/json/iso_3166-2.json';
		$this->assertTrue(is_file($f));

	}
}
