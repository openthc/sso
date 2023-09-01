<?php
/**
 *
 */

namespace OpenTHC\SSO\Test\D_Verify;

use Facebook\WebDriver\WebDriverBy;

class R_Password_Reset_Test extends \OpenTHC\SSO\Test\UI_Test_Case
{
	function test_password_reset()
	{
		self::$driver->get(sprintf('%s/auth/open?_t=%s'
			, getenv('OPENTHC_TEST_ORIGIN')
			, getenv('OPENTHC_TEST_HASH')
		));

		$html = self::$driver->getPageSource();
		$this->assertStringContainsString('Sign In', $html);

		$element = self::$driver->findElement(WebDriverBy::linkText('Forgot Password'));
		$element->click();


		$html = self::$driver->getPageSource();
		$this->assertStringContainsString('Password Reset', $html);
		$this->assertStringContainsString('Email', $html);

		$element = self::$driver->findElement(WebDriverBy::id('username'));
		$element->sendKeys(sprintf('%s@openthc.dev', getenv('OPENTHC_TEST_CONTACT')));

		// $element = self::$driver->findElement(WebDriverBy::id(''));
		$element = self::$driver->findElement(WebDriverBy::id('btn-password-reset'));
		$element->click();

		$html = self::$driver->getPageSource();
		$this->assertStringContainsString('Check Your Inbox', $html);

	}
}
