<?php
/**
 * Base Class for API Testing
 */

namespace Test\Core;

class A_Config_Test extends \Test\Base_Case
{
	function test_config()
	{
		$key_list = [
			'database/auth/hostname',
			'database/auth/username',
			'database/auth/password',
			'database/auth/database',
			'database/main/hostname',
			'database/main/username',
			'database/main/password',
			'database/main/database',
		];

		foreach ($key_list as $key) {
			$chk = \OpenTHC\Config::get($key);
			$this->assertNotEmpty($chk, sprintf('Key: "%s" is empty', $key));
		}

	}

}
