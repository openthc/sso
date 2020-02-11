<?php
/**
 * UI Authentication Tests
 */

namespace Test;

use Facebook\WebDriver\WebDriverBy;

class UI_Auth_Test extends \Test\UI_Test_Case
{
	public function testBasic()
	{
		// The Prime Site does a Meta-Refresh
		self::$driver->get(TEST_SITE);
		$src = self::$driver->getPageSource();
		$this->assertRegExp('/<meta http-equiv="refresh".+auth\/open/', $src);
		sleep(3); // Wait for refresh

		$url = self::$driver->getCurrentUrl();
		$this->assertRegExp('/auth\/open/', $url);

	}

	/**
	 *
	 */
	public function testSignIn()
	{
		self::$driver->get(TEST_SITE . '/auth/open');

		$element = self::$driver->findElement(WebDriverBy::name("username"));
		$element->sendKeys(USER_A_USERNAME);

		$element = self::$driver->findElement(WebDriverBy::name("password"));
		$element->sendKeys(USER_A_PASSWORD);

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=cao093', $url);

	}

	public function testSignInFailure()
	{
		self::$driver->get(TEST_SITE . '/auth/open');

		$element = self::$driver->findElement(WebDriverBy::name("username"));
		$element->sendKeys(USER_A_USERNAME);

		$element = self::$driver->findElement(WebDriverBy::name("password"));
		$element->sendKeys(USER_A_PASSWORD_FAIL);

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		// sleep(5);

		$url = self::$driver->getCurrentUrl();
		var_dump($url); //

		$this->assertStringContainsString('Invalid Username or Password', self::$driver->getPageSource());

	}

	public function testSignUp()
	{
		self::$driver->get(TEST_SITE . '/account/create');

		$element = self::$driver->findElement(WebDriverBy::id('account-region'));
		// Select Value: xxx/xx
		// $element->click();

		$btn = self::$driver->findElement(WebDriverBy::id('btn-region-next'));
		$btn->click();

		sleep(4);
		// Should be on Page Two

		$url = self::$driver->getCurrentUrl();
		var_dump($url); //


		$element = self::$driver->findElement(WebDriverBy::id('license-name'));
		$element->sendKeys('openthc');

		$username = sprintf("test+%s@openthc.com", time());
		$element = self::$driver->findElement(WebDriverBy::id("contact-name"));
		$element->sendKeys('Test User');

		$element = self::$driver->findElement(WebDriverBy::id("contact-email"));
		$element->sendKeys('test@openthc.com');

		$element = self::$driver->findElement(WebDriverBy::id("contact-phone"));
		$element->sendKeys('8559769333');

		$btn = self::$driver->findElement(WebDriverBy::id('btn-account-create'));
		$btn->click();

		// $this->assertStringContainsString('Account Confirmed', self::$driver->getPageSource());

		// $this->assertStringContainsString('Next, you will need to set a password', self::$driver->getPageSource());

		// $element = self::$driver->findElement(WebDriverBy::linkText("Set Password"));
		// $element->click();
		// sleep(3);

		// $element = self::$driver->findElement(WebDriverBy::name("password"));
		// $element->sendKeys($this->password);
		// $element = self::$driver->findElement(WebDriverBy::name("password-repeat"));
		// $element->sendKeys($this->password);

		// $element->submit();
		// sleep(5);

		// $element = self::$driver->findElement(WebDriverBy::linkText("Welcome!"));

	}

	public function xtestPasswordReset()
	{
		self::$driver->get(TEST_SITE);
		sleep(3);
		$this->assertStringContainsString('Sign In', self::$driver->getPageSource());

		$element = self::$driver->findElement(WebDriverBy::linkText("Reset Password"));
		$element->click();
		$this->assertStringContainsString('Password Reset', self::$driver->getPageSource());
		$this->assertStringContainsString('Email', self::$driver->getPageSource());

		$element = self::$driver->findElement(WebDriverBy::name("name"));
		$element->sendKeys(USER_A_USERNAME);

		$element = self::$driver->findElement(WebDriverBy::linkText("Request Password Reset"));
		$element->click();
		sleep(3);

		$this->assertStringContainsString('Password Reset Request Accepted', self::$driver->getPageSource());

	}

}
