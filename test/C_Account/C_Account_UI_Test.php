<?php
/**
 * Account Testing - UI
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\C_Account;

class C_Account_UI_Test extends \OpenTHC\SSO\Test\Browser\Base
{
	/**
	 * Change the user Name
	 */
	function test_change_name()
	{
		self::$wd->get(sprintf('%s/auth/open'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$node = $this->findElement('#username');
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_B']);

		// #password
		$node = $this->findElement('#password');
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		// #btn-auth-open
		$node = $this->findElement('#btn-auth-open');
		$node->click();
		$url0 = self::$wd->getCurrentUrl();
		// var_dump($url0);

		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/\/account$/', $url0);

		$node = $this->findElement('#contact-name');
		$val0 = $node->getAttribute('value');
		$val0 = '_' . $val0 . '_';
		$node->clear()->sendKeys($val0);

		$node = $this->findElement('button[value=contact-name-save]');
		$node->click();

		$node = $this->findElement('#contact-name');
		$val1 = $node->getAttribute('value');
		$this->assertEquals($val0, $val1);

		$url1 = self::$wd->getCurrentUrl();
		return $url1;
	}

	/**
	 * Change the user Email / Username
	 */
	function test_change_email()
	{
		self::$wd->get(sprintf('%s/profile'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$url0 = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/profile$/', $url0);

		$node = $this->findElement('#contact-email-unlock');
		$node->click();

		$node_input = $this->findElement('#contact-email');

	}

	/**
	 * Change the user Phone
	 */
	function test_change_phone()
	{
		self::$wd->get(sprintf('%s/profile'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$url0 = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/\/profile$/', $url0);

	}

	/**
	 * Test service connection - App
	 */
	function x_test_service_app()
	{
		self::$wd->get(sprintf('%s/profile'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$node = $this->findElement('a[data-service-name=app]');
		$node->click();

		$node = $this->findElement('#oauth2-authorize-permit');
		$node->click();

		$node = $this->findElement('#oauth2-permit-continue');
		$node->click();

		$this->assertMatchesRegularExpression('/Dashboard :: \w+/', self::$wd->getTitle());

		$url1 = self::$wd->getCurrentUrl();
		return $url1;
	}

	/**
	 * Test service connection - Directory
	 */
	function x_test_service_dir()
	{
		self::$wd->get(sprintf('%s/profile'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$node = $this->findElement('a[data-service-name=dir]');
		$node->click();

		$node = $this->findElement('#oauth2-authorize-permit');
		$node->click();

		$node = $this->findElement('#oauth2-permit-continue');
		$node->click();

		$this->assertMatchesRegularExpression('/Cannabis Company Directory/', self::$wd->getTitle());

		$url1 = self::$wd->getCurrentUrl();
		return $url1;}
}
