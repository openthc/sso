<?php
/**
 *
 */

namespace OpenTHC\SSO\Test\D_Verify;

use Facebook\WebDriver\WebDriverBy;

class R_Password_Reset_Test extends \OpenTHC\SSO\Test\UI_Test_Case
{

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
		self::$username = OPENTHC_TEST_CONTACT_B;
	}

	function test_password_reset()
	{
		self::$driver->get(sprintf('%s/auth/open'
			, OPENTHC_TEST_ORIGIN
		));

		$html = self::$driver->getPageSource();
		$this->assertStringContainsString('Sign In', $html);

		$element = self::$driver->findElement(WebDriverBy::linkText('Forgot Password'));
		$element->click();


		$html = self::$driver->getPageSource();
		$this->assertStringContainsString('Password Reset', $html);
		$this->assertStringContainsString('Email', $html);

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->sendKeys(self::$username);

		// $element = self::$driver->findElement(WebDriverBy::id(''));
		$element = self::$driver->findElement(WebDriverBy::id('btn-password-reset'));
		$element->click();

		$html = self::$driver->getPageSource();
		$this->assertStringNotContainsString('Invalid email', $html);
		$this->assertStringContainsString('Check Your Inbox', $html);

	}

	function test_password_reset_invalid($email = null)
	{
		self::$driver->get(sprintf('%s/auth/open'
			, OPENTHC_TEST_ORIGIN
		));

		$html = self::$driver->getPageSource();
		$this->assertStringContainsString('Sign In', $html);

		$element = self::$driver->findElement(WebDriverBy::linkText('Forgot Password'));
		$element->click();


		$element = self::$driver->findElement(WebDriverBy::id('username'));
		// $element->sendKeys(sprintf('%s@openthc.dev', OPENTHC_TEST_CONTACT));
		if (empty($email)) {
			$recurse = true;
			$email = sprintf('@openthc.example');
		}
		$element->sendKeys($email);

		// $element = self::$driver->findElement(WebDriverBy::id(''));
		$element = self::$driver->findElement(WebDriverBy::id('btn-password-reset'));
		$element->click();

		$html = self::$driver->getPageSource();
		$this->assertStringContainsString('Invalid email', $html);
		$this->assertStringNotContainsString('Check Your Inbox', $html);

		if ($recurse) {
			$this->test_password_reset_invalid('oneword');
			$this->test_password_reset_invalid('a sentenance not an email address');
			$this->test_password_reset_invalid('dev.openthc@test');
		}
	}
}
