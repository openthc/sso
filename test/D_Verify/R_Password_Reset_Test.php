<?php
/**
 *
 */

namespace OpenTHC\SSO\Test\D_Verify;

use Facebook\WebDriver\WebDriverBy;

class R_Password_Reset_Test extends \OpenTHC\SSO\Test\UI_Test_Case
{

	protected static $username;

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
		self::$username = OPENTHC_TEST_CONTACT_B;
	}

	function test_password_reset()
	{
		self::$wd->get(sprintf('%s/auth/open'
			, OPENTHC_TEST_ORIGIN
		));

		$html = self::$wd->getPageSource();
		$this->assertStringContainsString('Sign In', $html);

		$element = self::$wd->findElement(WebDriverBy::linkText('Forgot Password'));
		$element->click();


		$html = self::$wd->getPageSource();
		$this->assertStringContainsString('Password Reset', $html);
		$this->assertStringContainsString('Email', $html);

		$element = self::$wd->findElement(WebDriverBy::id('username'));
		$element->sendKeys(self::$username);

		// $element = self::$wd->findElement(WebDriverBy::id(''));
		$element = self::$wd->findElement(WebDriverBy::id('btn-password-reset'));
		$element->click();

		$html = self::$wd->getPageSource();
		$this->assertStringNotContainsString('Invalid email', $html);
		$this->assertStringContainsString('Check Your Inbox', $html);

	}

	function test_password_reset_invalid($email = null)
	{
		self::$wd->get(sprintf('%s/auth/open'
			, OPENTHC_TEST_ORIGIN
		));

		$html = self::$wd->getPageSource();
		$this->assertStringContainsString('Sign In', $html);

		$element = self::$wd->findElement(WebDriverBy::linkText('Forgot Password'));
		$element->click();


		$element = self::$wd->findElement(WebDriverBy::id('username'));
		// $element->sendKeys(sprintf('%s@openthc.dev', OPENTHC_TEST_CONTACT));
		if (empty($email)) {
			$recurse = true;
			$email = sprintf('@openthc.example');
		}
		$element->sendKeys($email);

		// $element = self::$wd->findElement(WebDriverBy::id(''));
		$element = self::$wd->findElement(WebDriverBy::id('btn-password-reset'));
		$element->click();

		$html = self::$wd->getPageSource();
		$this->assertStringContainsString('Invalid email', $html);
		$this->assertStringNotContainsString('Check Your Inbox', $html);

		if ($recurse) {
			$this->test_password_reset_invalid('oneword');
			$this->test_password_reset_invalid('a sentenance not an email address');
			$this->test_password_reset_invalid('dev.openthc@test');
		}
	}
}
