<?php
/**
 * UI Authentication Tests
 */

namespace Test\B_Basic;

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
	public function test_auth_open_fail()
	{
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_USERNAME'));

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=CAO-093', $url);
		$this->assertStringContainsString('Invalid Username or Password', self::$driver->getPageSource());

	}

	/**
	 *
	 */
	public function test_auth_create_account()
	{
		self::$driver->get(sprintf('https://%s/account/create', getenv('OPENTHC_TEST_HOST')));

		// Should Already Be Populated in Session
		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->value(getenv('OPENTHC_TEST_CONTACT_USERNAME'));

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->value(sprintf('invalid-password-%08x', rand(10000, 99999)));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		var_dump($url); //

		$this->assertStringContainsString('Invalid Username or Password', self::$driver->getPageSource());

	}

	/**
	 * Test auth open when contact stat=100
	 */
	public function test_auth_open_verify()
	{
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_USERNAME'));

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/verify?_=', $url);
		// $this->assertStringContainsString('Invalid Username or Password', self::$driver->getPageSource());

	}

	/**
	 * Test auth open when contact stat=200
	 */
	public function test_auth_open_live()
	{
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_USERNAME'));

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		// count(auth_company_contact.contact_id = auth_contact.id) > 1
		// $this->assertStringContainsString('/auth/init?_=', $url);

		// count(auth_company_contact.contact_id = auth_contact.id) == 1
		$this->assertStringContainsString('/account?_=', $url);
	}

	/**
	 * Test auth open when contact stat=410
	 */
	public function test_auth_open_gone()
	{
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_USERNAME'));

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/init?_=', $url);
		$this->assertStringContainsString('Invalid Account', self::$driver->getPageSource());
	}
}
