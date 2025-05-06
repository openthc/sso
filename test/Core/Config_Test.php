<?php
/**
 * Base Class for API Testing
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\Core;

class Config_Test extends \OpenTHC\SSO\Test\Base
{
	/**
	 * ack -o 'getenv\(.+\)'  test | cut -d':' -f2|sort |uniq -c | sort -nr
	 */
	function test_env()
	{
		$x = ini_get('variables_order');
		$this->assertFalse(strpos($x, 'E'));
		// if (strpos($x, 'E') === false) {
		// 	echo "AUTOLOADING ENV into \$_ENV == FALSE\n";
		// } else {
		// 	echo "AUTOLOADING ENV into \$_ENV == TRUE\n";
		// }
		// var_dump(ini_get('variables_order'));

		// ksort($_ENV);
		// var_dump($_ENV);

		// $env = getenv();
		// ksort($env);
		// var_dump($env);

		// ksort($_SERVER);
		// var_dump($_SERVER);

		$env_list = [
			'OPENTHC_TEST_ORIGIN',
			// 'OPENTHC_TEST_HTTP_DEBUG',
			'OPENTHC_TEST_WEBDRIVER_URL',
			'OPENTHC_TEST_CONTACT_A',
			'OPENTHC_TEST_CONTACT_B',
			'OPENTHC_TEST_CONTACT_C',
			'OPENTHC_TEST_CONTACT_PASSWORD',
			'OPENTHC_TEST_CONTACT_PHONE',
		];

		foreach ($env_list as $x) {
			$this->assertArrayHasKey($x, $_ENV);
			$this->assertNotEmpty($_ENV[$x], sprintf('$_ENV missing "%s"', $x));
			// $this->assertNotEmpty(getenv($x), sprintf('getenv missing "%s"', $x));
			// $this->assertNotEmpty(constant($x), sprintf('Constant missing "%s"', $x));
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
