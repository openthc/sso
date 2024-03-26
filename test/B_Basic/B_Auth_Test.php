<?php
/**
 * UI Authentication Tests
 */

namespace OpenTHC\SSO\Test\B_Basic;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class B_Auth_Test extends \OpenTHC\SSO\Test\UI_Test_Case
{
	protected static $username;

	public static function setupBeforeClass(): void
	{
		parent::setupBeforeClass();
		self::$username = OPENTHC_TEST_CONTACT_A;
	}

	/**
	 *
	 */
	public function test_home_redirect()
	{
		// The Prime Site does a Meta-Refresh
		self::$driver->get(OPENTHC_TEST_ORIGIN);
		$src = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/<meta http-equiv="refresh".+auth\/open/', $src);
		sleep(3); // Wait for refresh

		$url = self::$driver->getCurrentUrl();
		$this->assertMatchesRegularExpression('/auth\/open/', $url);

	}

	/**
	 * Test Test Bad Account, Bad Password
	 */
	public function test_auth_open_fail_username()
	{
		self::$driver->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(sprintf('invalid-%s@openthc.example', _ulid()));

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys('WRONG_PASSWORD');

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=CAO-093', $url);
		$this->assertStringContainsString('Invalid Username or Password', self::$driver->getPageSource());

	}

	/**
	 * Test Test Good Account, Bad Password
	 */
	public function test_auth_open_fail_password()
	{
		self::$driver->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys('test+a@openthc.example');

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys('WRONG_PASSWORD');

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=CAO-153', $url);
		$this->assertStringContainsString('Invalid Username or Password', self::$driver->getPageSource());

	}

	/**
	 *
	 */
	public function test_auth_wellknown_reset()
	{
		//
		self::$driver->get(sprintf('%s/.well-known/change-password', OPENTHC_TEST_ORIGIN));

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?a=password-reset', $url);

	}

	/**
	 * Test auth open when contact stat=100
	 * @todo needs to have the account actually added to test this
	 */
	public function x_test_auth_open_verify()
	{
		// self::$driver->manage()->deleteAllCookies();
		self::$driver->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(OPENTHC_TEST_CONTACT_PASSWORD);

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();

		$this->assertStringContainsString('/done?e=CAO-144', $url);
		$this->assertStringContainsString('Account Pending', self::$driver->getPageSource());

	}

	/**
	 * Test auth open when contact stat=200
	 */
	public function x_test_auth_open_live()
	{
		// self::$driver->manage()->deleteAllCookies();
		self::$driver->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(OPENTHC_TEST_CONTACT_PASSWORD);

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		var_dump($url);
		$this->assertTrue(true);
		$this->assertStringContainsString('/auth/init?_=', $url);
		// $this->assertStringContainsString('/verify?_=', $url);
	}

	/**
	 * Test auth open when contact stat=410
	 */
	public function x_test_auth_open_gone()
	{
		// self::$driver->manage()->deleteAllCookies();
		self::$driver->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(OPENTHC_TEST_CONTACT_PASSWORD);

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/init?_=', $url);
		$this->assertStringContainsString('Invalid Account', self::$driver->getPageSource());
	}
}
