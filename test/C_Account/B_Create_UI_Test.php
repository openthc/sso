<?php
/**
 * Account Create Testing - UI
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\C_Account;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class B_Create_UI_Test extends \OpenTHC\SSO\Test\Browser\Base
{

	static $username;

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
		self::$username = strtolower(sprintf('test+%s-ui@openthc.example', _ulid()));
	}

	/**
	 *
	 */
	function test_account_create()
	{
		self::$wd->get(sprintf('%s/account/create'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$node = $this->findElement('#alert-test-mode');
		$txt = $node->getText();
		$this->assertEquals('TEST MODE', $txt, 'Apache2 Environment missing variable: SetEnv OPENTHC_TEST "TEST"');

		$node = $this->findElement('#contact-name');
		$node->sendKeys(self::$username);

		$node = $this->findElement('#contact-email');
		$node->sendKeys(self::$username);

		$node = $this->findElement('#contact-phone');
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PHONE']);

		$node = $this->findElement('#btn-account-create');
		$node->click();

		// Should Submit and then take us to the /DONE page, with the trigger in the URL
		$url1 = self::$wd->getCurrentUrl();
		//$this->assertMatchesRegularExpression('/\/done\?e=CAC\-0?\d+/', $url1);
		$this->assertMatchesRegularExpression('/\/done\?e=CAC-\d+/', $url1);
		//$this->assertMatchesRegularExpression('/\/done\?e=CAC\-0?\d+.+r=/', $url1); // Has Test Link
		// $this->assertStringContainsString('/done?e=CAC-111', $url);
		// $this->assertStringContainsString('Please check your email to confirm your account', self::$wd->getPageSource());

		/*
		$url1 = preg_match('/r=(.+)$/', $url1, $m) ? $m[1] : '';
		$url1 = rawurldecode($url1);
		*/

		$node = $this->findElement('#alert-test-link');
		$a = $node->findElement(WebDriverBy::cssSelector('a'));
		$url1 = $a->getAttribute('href');
		$this->assertNotEmpty($url1, 'Apache2 Environment missing variable: SetEnv OPENTHC_TEST "TEST"');

		return $url1;

		// $this->assertStringContainsString('Account Confirmed', self::$wd->getPageSource());
		// $this->assertStringContainsString('Next, you will need to set a password', self::$wd->getPageSource());

		// $element = $this->findElement(WebDriverBy::linkText("Set Password"));
		// $element->click();

		// $element = $this->findElement(WebDriverBy::name("password"));
		// $element->sendKeys($this->password);
		// $element = $this->findElement(WebDriverBy::name("password-repeat"));
		// $element->sendKeys($this->password);

		// $element->submit();

		// $element = $this->findElement(WebDriverBy::linkText("Welcome!"));

	}

	/**
	 * @depends test_account_create
	 */
	function test_verify_password($url0)
	{
		$url0 = ltrim($url0, '/');
		$this->assertNotEmpty($url0);
		self::$wd->get(sprintf('%s/%s', $_ENV['OPENTHC_TEST_ORIGIN'], $url0));

		$url1 = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/verify\/password.+/', $url1);

		$node = $this->findElement('#password0');
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$node = $this->findElement('#password1');
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$node = $this->findElement('#btn-password-update');
		$node->click();

		// Bounces

		$url2 = self::$wd->getCurrentUrl();

		return $url2;

	}

	/**
	 * @depends test_verify_password
	 */
	function test_verify_location($url0)
	{
		$this->assertMatchesRegularExpression('/\/verify\/location.+/', $url0);

		// $html = self::$wd->getPageSource();
		// $this->assertStringContainsString('TEST MODE', $html);
		// $this->assertStringContainsString('Verify Profile Location', $html);

		$e = $this->findElement('#contact-iso3166-1');
		$select = new \Facebook\WebDriver\WebDriverSelect($e);
		$select->selectByValue('US');

		$node = $this->findElement('#btn-location-save');
		$node->click();

		// Bounces
		$url1 = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/verify\/location.+/', $url1);

		/*
		$node = $this->findElement('#contact-iso3166-1');
		var_dump($node);
		$node = new WebDriverSelect($node);
		var_dump($node);
		$node->selectByValue('US');

		$node = $this->findElement('#contact-iso3166-2');
		$node = new WebDriverSelect($node);
		$node->selectByValue('US-WA');
		*/


		$node = $this->findElement('#btn-location-save');
		$node->click();

		$url2 = self::$wd->getCurrentUrl();

		return $url2;

	}

	/**
	 * @depends test_verify_location
	 */
	function test_verify_timezone($url0)
	{
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/\/verify\/timezone\?_=.+/', $url0);

		$node = $this->findElement('#contact-timezone');
		$node = new \Facebook\WebDriver\WebDriverSelect($node);
		$node->selectByValue('America/New_York');

		// Time Zone
		$node = $this->findElement('#btn-timezone-save');
		$node->click();

		$url1 = self::$wd->getCurrentUrl();

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

		$node = $this->findElement('#contact-phone');
		$node->sendKeys('+12125551212');

		$node = $this->findElement('#btn-contact-phone-verify-send');
		$node->click();

		$url1 = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/verify\/phone\?_=.+c=\w{6}/', $url1);
		$code = preg_match('/c=(\w+)/', $url1, $m) ? $m[1] : '';

		$node = $this->findElement('#phone-verify-code');
		$node->sendKeys($code);

		$node = $this->findElement('#btn-contact-phone-verify-save');
		$node->click();

		$url2 = self::$wd->getCurrentUrl();

		return $url2;

	}
	*/

	/**
	 * @depends test_verify_timezone
	 */
	function test_verify_company($url0)
	{
		$this->markTestSkipped('We do not think this is a feature that will stay around.');
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/\/verify\/company\?_=.+/', $url0);

		$node = $this->findElement('#btn-company-skip');
		$node->click();

		$url1 = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/done/', $url1);

		return $url1;

	}

	/**
	 * @depends test_verify_location
	 */
	function test_sign_in_new($url0)
	{
		$node = $this->findElement(WebDriverBy::cssSelector("[href^='/auth/open']"));
		$node->click();

		// #username
		$node = $this->findElement('#username');
		$v = $node->getAttribute('value');
		$this->assertEquals($v, self::$username);

		// #password
		$node = $this->findElement('#password');
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		// #btn-auth-open
		$node = $this->findElement('#btn-auth-open');
		$node->click();

		$url1 = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/account/', $url1);
		$src = self::$wd->getPageSource();
		$this->assertDoesNotMatchRegularExpression('/Invalid Username or Password/', $src);
	}
}
