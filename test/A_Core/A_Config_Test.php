<?php
/**
 * Base Class for API Testing
 */

namespace Test\A_Core;

class A_Config_Test extends \Test\Base_Case
{
	function test_env()
	{
		$env_list = [
			'OPENTHC_TEST_HOST',
			'OPENTHC_TEST_HASH',
			'OPENTHC_TEST_CONTACT_USERNAME',
			'OPENTHC_TEST_CONTACT_PASSWORD',
			'OPENTHC_TEST_CONTACT_EMAIL',
			'OPENTHC_TEST_CONTACT_PHONE',
		];

		foreach ($env_list as $x) {
			$v = getenv($x);
			$this->assertNotEmpty($v, sprintf('Environment "%s" missing', $x));
		}
	}

	/**
	 *
	 */
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
