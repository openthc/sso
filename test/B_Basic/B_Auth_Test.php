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
		self::$username = sprintf('%s@openthc.dev', getenv('OPENTHC_TEST_CONTACT'));
	}

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
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
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
	public function test_auth_wellknown_reset()
	{
		//
		self::$driver->get(sprintf('https://%s/.well-known/change-password', getenv('OPENTHC_TEST_HOST')));

		$url = self::$driver->getCurrentUrl();
		// var_dump($url);
		$this->assertStringContainsString('/auth/open?a=password-reset', $url);
		// $this->assertTrue(true);

	}

	/**
	 *
	 */
	public function x_test_auth_create_account()
	{
		// self::$driver->manage()->deleteAllCookies();
		self::$driver->get(sprintf('https://%s/account/create?_t=%s'
			, getenv('OPENTHC_TEST_HOST')
			, getenv('OPENTHC_TEST_HASH')
		));

		// Should Already Be Populated in Session
		$element = self::$driver->findElement(WebDriverBy::id('contact-name'));
		$element->clear();
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT'));

		$element = self::$driver->findElement(WebDriverBy::id('contact-email'));
		$element->clear();
		$element->sendKeys(self::$username);

		$btn = self::$driver->findElement(WebDriverBy::id('btn-account-create'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();

		$this->assertStringContainsString('/done?e=CAC-111', $url);
		$this->assertStringContainsString('Please check your email to confirm your account', self::$driver->getPageSource());

		//
		$url_info = parse_url($url);

		var_dump($url_info);

		$arg = __parse_str($url_info['query']);

	}

	/**
	 * Test auth open when contact stat=100
	 */
	public function x_test_auth_open_verify()
	{
		// self::$driver->manage()->deleteAllCookies();
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

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
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

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
		self::$driver->get(sprintf('https://%s/auth/open', getenv('OPENTHC_TEST_HOST')));

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->clear();
		$element->sendKeys(self::$username);

		$element = self::$driver->findElement(WebDriverBy::id('password'));
		$element->clear();
		$element->sendKeys(getenv('OPENTHC_TEST_CONTACT_PASSWORD'));

		$btn = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$btn->click();

		$url = self::$driver->getCurrentUrl();
		$this->assertStringContainsString('/auth/init?_=', $url);
		$this->assertStringContainsString('Invalid Account', self::$driver->getPageSource());
	}
}
