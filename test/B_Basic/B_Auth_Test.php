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
		self::$wd->get(OPENTHC_TEST_ORIGIN);
		$src = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/<meta http-equiv="refresh".+auth\/open/', $src);
		sleep(3); // Wait for refresh

		$url = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/auth\/open/', $url);

	}

	/**
	 * Test Test Bad Account, Bad Password
	 */
	public function test_auth_open_fail_username()
	{
		self::$wd->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$wd->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(sprintf('invalid-%s@openthc.example', _ulid()));

		$element = self::$wd->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys('WRONG_PASSWORD');

		$btn = self::$wd->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=CAO-093', $url);
		$this->assertStringContainsString('Invalid Username or Password', self::$wd->getPageSource());

	}

	/**
	 * Test Test Good Account, Bad Password
	 */
	public function test_auth_open_fail_password()
	{
		self::$wd->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$wd->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$wd->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys('WRONG_PASSWORD');

		$btn = self::$wd->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=CAO-153', $url);
		$this->assertStringContainsString('Invalid Username or Password', self::$wd->getPageSource());

	}

	/**
	 *
	 */
	public function test_auth_wellknown_reset()
	{
		//
		self::$wd->get(sprintf('%s/.well-known/change-password', OPENTHC_TEST_ORIGIN));

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?a=password-reset', $url);

	}

	/**
	 * Test auth open when contact stat=100
	 * @todo needs to have the account actually added to test this
	 */
	public function x_test_auth_open_verify()
	{
		// self::$wd->manage()->deleteAllCookies();
		self::$wd->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$wd->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$wd->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(OPENTHC_TEST_CONTACT_PASSWORD);

		$btn = self::$wd->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$wd->getCurrentUrl();

		$this->assertStringContainsString('/done?e=CAO-144', $url);
		$this->assertStringContainsString('Account Pending', self::$wd->getPageSource());

	}

	/**
	 * Test auth open when contact stat=200
	 */
	public function x_test_auth_open_live()
	{
		// self::$wd->manage()->deleteAllCookies();
		self::$wd->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$wd->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$wd->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(OPENTHC_TEST_CONTACT_PASSWORD);

		$btn = self::$wd->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$wd->getCurrentUrl();
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
		// self::$wd->manage()->deleteAllCookies();
		self::$wd->get(sprintf('%s/auth/open', OPENTHC_TEST_ORIGIN));

		$element = self::$wd->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$wd->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(OPENTHC_TEST_CONTACT_PASSWORD);

		$btn = self::$wd->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/init?_=', $url);
		$this->assertStringContainsString('Invalid Account', self::$wd->getPageSource());
	}
}
