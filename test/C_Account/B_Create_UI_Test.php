<?php
/**
 * Account Create Testing - UI
 */

namespace OpenTHC\SSO\Test\C_Account;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class B_Create_UI_Test extends \OpenTHC\SSO\Test\UI_Test_Case
{

	static $username;

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
		self::$username = sprintf('%s-ui@openthc.example', _ulid());
	}

	/**
	 *
	 */
	function test_account_create()
	{
		self::$driver->get(sprintf('%s/account/create'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$node = self::$driver->findElement(WebDriverBy::id('alert-test-mode'));
		$txt = $node->getText();
		$this->assertEquals('TEST MODE', $txt, 'Apache2 Environment missing variable: SetEnv OPENTHC_TEST "TEST"');

		$node = self::$driver->findElement(WebDriverBy::id('contact-name'));
		$node->sendKeys(self::$username);

		$node = self::$driver->findElement(WebDriverBy::id('contact-email'));
		$node->sendKeys(self::$username);

		$node = self::$driver->findElement(WebDriverBy::id('contact-phone'));
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PHONE']);

		$node = self::$driver->findElement(WebDriverBy::id('btn-account-create'));
		$node->click();

		// Should Submit and then take us to the /DONE page, with the trigger in the URL
		$url1 = self::$driver->getCurrentUrl();
		//$this->assertMatchesRegularExpression('/\/done\?e=CAC\-0?\d+/', $url1);
		$this->assertMatchesRegularExpression('/\/done\?e=CAC-\d+/', $url1);
		//$this->assertMatchesRegularExpression('/\/done\?e=CAC\-0?\d+.+r=/', $url1); // Has Test Link
		// $this->assertStringContainsString('/done?e=CAC-111', $url);
		// $this->assertStringContainsString('Please check your email to confirm your account', self::$driver->getPageSource());

		/*
		$url1 = preg_match('/r=(.+)$/', $url1, $m) ? $m[1] : '';
		$url1 = rawurldecode($url1);
		*/

		$node = self::$driver->findElement(WebDriverBy::id('alert-test-link'));
		$a = $node->findElement(WebDriverBy::cssSelector('a'));
		$url1 = $a->getAttribute('href');
		$this->assertNotEmpty($url1, 'Apache2 Environment missing variable: SetEnv OPENTHC_TEST "TEST"');

		return $url1;

		// $this->assertStringContainsString('Account Confirmed', self::$driver->getPageSource());
		// $this->assertStringContainsString('Next, you will need to set a password', self::$driver->getPageSource());

		// $element = self::$driver->findElement(WebDriverBy::linkText("Set Password"));
		// $element->click();

		// $element = self::$driver->findElement(WebDriverBy::name("password"));
		// $element->sendKeys($this->password);
		// $element = self::$driver->findElement(WebDriverBy::name("password-repeat"));
		// $element->sendKeys($this->password);

		// $element->submit();

		// $element = self::$driver->findElement(WebDriverBy::linkText("Welcome!"));

	}

	/**
	 * @depends test_account_create
	 */
	function test_verify_password($url0)
	{
		$url0 = ltrim($url0, '/');
		$this->assertNotEmpty($url0);
		self::$driver->get(sprintf('%s/%s', $_ENV['OPENTHC_TEST_ORIGIN'], $url0));

		$url1 = self::$driver->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/verify\/password.+/', $url1);

		$node = self::$driver->findElement(WebDriverBy::id('password0'));
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$node = self::$driver->findElement(WebDriverBy::id('password1'));
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$node = self::$driver->findElement(WebDriverBy::id('btn-password-update'));
		$node->click();

		// Bounces

		$url2 = self::$driver->getCurrentUrl();

		return $url2;

	}

	/**
	 * @depends test_verify_password
	 */
	function test_verify_location($url0)
	{
		$this->assertMatchesRegularExpression('/\/verify\/location.+/', $url0);

		// $html = self::$driver->getPageSource();
		// $this->assertStringContainsString('TEST MODE', $html);
		// $this->assertStringContainsString('Verify Profile Location', $html);

		$node = self::$driver->findElement(WebDriverBy::id('btn-location-save'));
		$node->click();

		// Bounces
		$url1 = self::$driver->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/verify\/location.+/', $url1);

		/*
		$node = self::$driver->findElement(WebDriverBy::id('contact-iso3166-1'));
		var_dump($node);
		$node = new WebDriverSelect($node);
		var_dump($node);
		$node->selectByValue('US');

		$node = self::$driver->findElement(WebDriverBy::id('contact-iso3166-2'));
		$node = new WebDriverSelect($node);
		$node->selectByValue('US-WA');
		*/


		$node = self::$driver->findElement(WebDriverBy::id('btn-location-save'));
		$node->click();

		$url2 = self::$driver->getCurrentUrl();

		return $url2;

	}

	/**
	 * @depends test_verify_location
	 */
	function test_verify_timezone($url0)
	{
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/\/verify\/timezone\?_=.+/', $url0);

		// Time Zone
		$node = self::$driver->findElement(WebDriverBy::id('btn-timezone-save'));
		$node->click();

		$url1 = self::$driver->getCurrentUrl();

		return $url1;

	}

	/**
	 * Dropped Requirement
	 */
	/*
	function test_verify_phone($url0)
	{
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/\/verify\/phone\?_=.+/', $url0);

		$node = self::$driver->findElement(WebDriverBy::id('contact-phone'));
		$node->sendKeys('+12125551212');

		$node = self::$driver->findElement(WebDriverBy::id('btn-contact-phone-verify-send'));
		$node->click();

		$url1 = self::$driver->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/verify\/phone\?_=.+c=\w{6}/', $url1);
		$code = preg_match('/c=(\w+)/', $url1, $m) ? $m[1] : '';

		$node = self::$driver->findElement(WebDriverBy::id('phone-verify-code'));
		$node->sendKeys($code);

		$node = self::$driver->findElement(WebDriverBy::id('btn-contact-phone-verify-save'));
		$node->click();

		$url2 = self::$driver->getCurrentUrl();

		return $url2;

	}
	*/

	/**
	 * @depends test_verify_timezone
	 */
	function test_verify_company($url0)
	{
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/\/verify\/company\?_=.+/', $url0);

		$node = self::$driver->findElement(WebDriverBy::id('btn-company-skip'));
		$node->click();

		$url1 = self::$driver->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/done/', $url1);

		return $url1;

	}

	/**
	 * @depends test_verify_company
	 */
	function test_sign_in_new($url0)
	{
		$node = self::$driver->findElement(WebDriverBy::cssSelector("[href^='/auth/open']"));
		$node->click();

		// #username
		$node = self::$driver->findElement(WebDriverBy::id('username'));
		$node->sendKeys(self::$username);

		// #password
		$node = self::$driver->findElement(WebDriverBy::id('password'));
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		// #btn-auth-open
		$node = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$node->click();

		$url1 = self::$driver->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/account/', $url1);
		$src = self::$driver->getPageSource();
		$this->assertDoesNotMatchRegularExpression('/Invalid Username or Password/', $src);
	}
}
