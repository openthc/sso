<?php
/**
 * Base Class for API Testing
 */

namespace Test\Core;

class Config_Test extends \Test\Base_Test_Case
{
	function test_config()
	{
		$cfg = \OpenTHC\Config::get(null);
		// var_dump($cfg);

		$this->assertIsArray($cfg);
		$this->assertArrayHasKey('application', $cfg);
		$this->assertArrayHasKey('database', $cfg);
		$this->assertIsArray($cfg['database']);
		foreach ([ 'hostname', 'database' , 'username', 'password' ] as $x) {
			$this->assertArrayHasKey($x, $cfg['database']);
			$this->assertIsString($cfg['database'][$x]);
		}
		$this->assertArrayHasKey('redis', $cfg);
		$this->assertArrayHasKey('openthc_app', $cfg);
		$this->assertArrayHasKey('openthc_cic', $cfg);

	}

}
