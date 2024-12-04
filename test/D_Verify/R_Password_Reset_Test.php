<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\D_Verify;

use Facebook\WebDriver\WebDriverBy;

class R_Password_Reset_Test extends \OpenTHC\SSO\Test\Browser\Base
{
	function test_password_reset()
	{
		self::$wd->get(sprintf('%s/auth/open'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$html = self::$wd->getPageSource();
		$this->assertStringContainsString('Sign In', $html);

		$element = $this->findElement(WebDriverBy::linkText('Forgot Password'));
		$element->click();


		$html = self::$wd->getPageSource();
		$this->assertStringContainsString('Password Reset', $html);
		$this->assertStringContainsString('Email', $html);

		$element = $this->findElement('#username');
		$element->sendKeys($_ENV['OPENTHC_TEST_CONTACT_A']);

		$element = $this->findElement('#btn-password-reset');
		$element->click();

		$html = self::$wd->getPageSource();
		$this->assertStringNotContainsString('Invalid email', $html);
		$this->assertStringContainsString('Check Your Inbox', $html);

	}

	function test_password_reset_invalid($email = null)
	{
		self::$wd->get(sprintf('%s/auth/open'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$html = self::$wd->getPageSource();
		$this->assertStringContainsString('Sign In', $html);

		$element = self::$wd->findElement(WebDriverBy::linkText('Forgot Password'));
		$element->click();


		$element = $this->findElement('#username');
		// $element->sendKeys(sprintf('%s@openthc.dev', $_ENV['OPENTHC_TEST_CONTACT']));
		if (empty($email)) {
			$recurse = true;
			$email = sprintf('@openthc.example');
		}
		$element->sendKeys($email);

		$element = $this->findElement('#btn-password-reset');
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
