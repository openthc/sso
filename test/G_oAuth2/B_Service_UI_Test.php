<?php
/**
 * Test Default Service Connections in SSO
 */

namespace OpenTHC\SSO\Test\G_oAuth2;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class B_Service_UI_Test extends \OpenTHC\SSO\Test\UI_Test_Case
{

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
		self::$driver->get(sprintf('%s/auth/open?_t=%s'
			, getenv('OPENTHC_TEST_ORIGIN')
			, getenv('OPENTHC_TEST_HASH')
		));

		$username = getenv('OPENTHC_TEST_CONTACT_USERNAME');
		$node = self::$driver->findElement(WebDriverBy::id('username'));
		$node->sendKeys($username);

		$password = getenv('OPENTHC_TEST_CONTACT_PASSWORD');
		$node = self::$driver->findElement(WebDriverBy::id('password'));
		$node->sendKeys($password);

		$node = self::$driver->findElement(WebDriverBy::id('btn-auth-open'));
		$node->click();
	}

	function test_auth_pass_app($url = null)
	{

		self::$driver->get(sprintf('%s/account?_t=%s'
			, getenv('OPENTHC_TEST_ORIGIN')
			, getenv('OPENTHC_TEST_HASH')
		));

		$node = self::$driver->findElement(WebDriverBy::cssSelector('[data-service-name=app]'));
		$node->click();

		$window_handles = self::$driver->getWindowHandles();
		$sso_tab = $window_handles[0];
		$app_tab = $window_handles[ array_key_last($window_handles) ];
		self::$driver->switchTo()->window($app_tab);

		// Application Authorization
		$html = self::$driver->getPageSource();
		$node = self::$driver->findElement(WebDriverBy::cssSelector('#oauth2-authorize-permit'));
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		$html = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = self::$driver->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		// Dashboard
		$html = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/Dashboard ::/', $html);

		self::$driver->switchTo()->window($sso_tab);
		return self::$driver->getCurrentUrl();
	}

	function test_auth_pass_b2b()
	{
		$node = self::$driver->findElement(WebDriverBy::cssSelector('[data-service-name="b2b marketplace"]'));
		$node->click();

		$window_handles = self::$driver->getWindowHandles();
		$sso_tab = $window_handles[0];
		$b2b_tab = $window_handles[ array_key_last($window_handles) ];
		self::$driver->switchTo()->window($b2b_tab);

		// Application Authorization
		$html = self::$driver->getPageSource();
		$node = self::$driver->findElement(WebDriverBy::cssSelector('#oauth2-authorize-permit'));
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		$html = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = self::$driver->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		// Market Search
		$html = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/Market/', $html);
		$this->assertMatchesRegularExpression('/Signed in as:/', $html);

		self::$driver->switchTo()->window($sso_tab);
		return self::$driver->getCurrentUrl();
	}

	function test_auth_pass_dir()
	{
		$this->assertTrue(true);
	}

	function test_auth_pass_pos()
	{
		$node = self::$driver->findElement(WebDriverBy::cssSelector('[data-service-name="retail pos"]'));
		$node->click();

		$window_handles = self::$driver->getWindowHandles();
		$sso_tab = $window_handles[0];
		$pos_tab = $window_handles[ array_key_last($window_handles) ];
		self::$driver->switchTo()->window($pos_tab);

		// Application Authorization
		$html = self::$driver->getPageSource();
		$node = self::$driver->findElement(WebDriverBy::cssSelector('#oauth2-authorize-permit'));
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		$html = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = self::$driver->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		// Dashboard
		$html = self::$driver->getPageSource();
		$this->assertMatchesRegularExpression('/Dashboard/', $html);
		$this->assertMatchesRegularExpression('/POS/', $html);

		self::$driver->switchTo()->window($sso_tab);
		return self::$driver->getCurrentUrl();
	}

	function test_auth_pass_lab()
	{
		$this->assertTrue(true);
	}

}
