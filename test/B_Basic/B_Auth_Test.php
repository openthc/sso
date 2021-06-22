<?php
/**
 * UI Authentication Tests
 */

namespace Test\B_Base;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class B_Auth_Test extends \Test\UI_Test_Case
{
	/**
	 *
	 */
	public function test_home_redirect()
	{
		// The Prime Site does a Meta-Refresh
		self::$driver->get(sprintf('https://%s/', getenv('OPENTHC_TEST_HOST')));
		$src = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/<meta http-equiv="refresh".+auth\/open/', $src);
		sleep(3); // Wait for refresh

		$url = self::$driver->getCurrentUrl();
		$this->assertMatchesRegularExpression('/auth\/open/', $url);

	}

	/**
	 *
	 */
	public function test_auth_open()
	{
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_USERNAME'));

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=cao093', $url);

	}

	/**
	 *
	 */
	public function test_auth_open_init()
	{
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id("username"));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_USERNAME'));

		$element = self::$driver->findElement(WebDriverBy::id("password"));
		$element->sendKeys(sprintf('invalid-password-%08x', rand(10000, 99999)));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		// sleep(5);

		$url = self::$driver->getCurrentUrl();
		var_dump($url); //

		$this->assertStringContainsString('Invalid Username or Password', self::$driver->getPageSource());

	}

}
