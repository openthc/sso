<?php
/**
 * Base Class for API Testing
 */

namespace OpenTHC\SSO\Test\A_Core;

class A_Config_Test extends \OpenTHC\SSO\Test\Base_Case
{
	/**
	 * ack -o 'getenv\(.+\)'  test | cut -d':' -f2|sort |uniq -c | sort -nr
	 */
	function test_env()
	{
		$env_list = [
			'OPENTHC_TEST_ORIGIN',
			'OPENTHC_TEST_CONTACT_A',
			'OPENTHC_TEST_CONTACT_B',
			'OPENTHC_TEST_CONTACT_C',
			'OPENTHC_TEST_CONTACT_PASSWORD',
			// 'OPENTHC_TEST_CONTACT_PHONE',
			'OPENTHC_TEST_WEBDRIVER_URL',
		];

		foreach ($env_list as $x) {
			$this->assertNotEmpty(constant($x), sprintf('Constant "%s" missing', $x));
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

			'redis/hostname',

			'openthc/sso/id',
			'openthc/sso/origin',
			'openthc/sso/public',
			'openthc/sso/secret',

			// 'openthc/www/id',
			// 'openthc/www/origin',
			// 'openthc/www/secret',
			'openthc/sso/test/sk',
		];

		foreach ($key_list as $key) {
			$chk = \OpenTHC\Config::get($key);
			$this->assertNotEmpty($chk, sprintf('Key: "%s" is empty', $key));
		}

	}

}
