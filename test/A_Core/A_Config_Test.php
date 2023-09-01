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
			'OPENTHC_TEST_HASH',
			'OPENTHC_TEST_CONTACT',
			'OPENTHC_TEST_CONTACT_PASSWORD',
			'OPENTHC_TEST_CONTACT_PHONE',
			'OPENTHC_TEST_WEBDRIVER_URL',
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

			'openthc/sso/id',
			'openthc/sso/origin',
			'openthc/sso/origin-sk',
			'openthc/sso/secret',

			// 'openthc/www/id',
			// 'openthc/www/origin',
			// 'openthc/www/secret',
		];

		foreach ($key_list as $key) {
			$chk = \OpenTHC\Config::get($key);
			$this->assertNotEmpty($chk, sprintf('Key: "%s" is empty', $key));
		}

	}

}
