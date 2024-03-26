<?php
/**
 * Account Testing - UI
 */

namespace OpenTHC\SSO\Test\C_Account;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class C_Account_UI_Test extends \OpenTHC\SSO\Test\UI_Test_Case
{

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();

		self::$driver->get(sprintf('%s/auth/open'
			, OPENTHC_TEST_ORIGIN
		));

		$node = self::$driver->findElement(WebDriverBy::id('username'));
		$node->sendKeys(OPENTHC_TEST_CONTACT_A);

		// #password
		$node = self::$driver->findElement(WebDriverBy::id('password'));
		$node->sendKeys(OPENTHC_TEST_CONTACT_PASSWORD);

		// #btn-auth-open
		$node = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$node->click();
	}

	/**
	 * Change the user Name
	 */
	function test_change_name($url0 = null)
	{
		if (empty($url0)) {
			$url0 = self::$driver->getCurrentUrl();
		}

		self::$driver->get(sprintf('%s/account'
			, OPENTHC_TEST_ORIGIN
		));

		$node = self::$driver->findElement(WebDriverBy::id('contact-name'));
		$val0 = $node->getText();
		$val0 = $val0 . '_';
		$node->sendKeys($val0);

		$node = self::$driver->findElement(WebDriverBy::cssSelector('button[value=contact-name-save]'));
		$node->click();

		$node = self::$driver->findElement(WebDriverBy::id('contact-name'));
		$val1 = $node->getText();
		$this->assertEquals($val0, $val1);

		$url1 = self::$driver->getCurrentUrl();
		return $url1;
	}

	/**
	 * Change the user Email / Username
	 * @depends test_change_name
	 */
	function test_change_email($url0)
	{}

	/**
	 * Change the user Phone
	 * @depends test_change_name
	 */
	function test_change_phone($url0)
	{}

	/**
	 * Test service connection - App
	 * @depends test_change_name
	 */
	function test_service_app($url0)
	{
		$node = self::$driver->findElement(WebDriverBy::cssSelector('a[data-service-name=app]'));
		$node->click();

		$node = self::$driver->findElement(WebDriverBy::id('oauth2-authorize-permit'));
		$node->click();

		$node = self::$driver->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		$this->assertMatchesRegularExpression('/Dashboard :: \w+/', self::$driver->getTitle());

		$url1 = self::$driver->getCurrentUrl();
		return $url1;
	}

	/**
	 * Test service connection - Directory
	 * @depends test_change_name
	 */
	function test_service_dir($url0)
	{
		$node = self::$driver->findElement(WebDriverBy::cssSelector('a[data-service-name=directory]'));
		$node->click();

		$node = self::$driver->findElement(WebDriverBy::id('oauth2-authorize-permit'));
		$node->click();

		$node = self::$driver->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		$this->assertMatchesRegularExpression('/Cannabis Company Directory/', self::$driver->getTitle());

		$url1 = self::$driver->getCurrentUrl();
		return $url1;}
}
