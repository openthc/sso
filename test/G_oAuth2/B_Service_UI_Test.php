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
		self::$wd->get(sprintf('%s/auth/open'
			, OPENTHC_TEST_ORIGIN
		));

		$username = OPENTHC_TEST_CONTACT_A;
		$node = self::$wd->findElement(WebDriverBy::id('username'));
		$node->sendKeys($username);

		$password = OPENTHC_TEST_CONTACT_PASSWORD;
		$node = self::$wd->findElement(WebDriverBy::id('password'));
		$node->sendKeys($password);

		$node = self::$wd->findElement(WebDriverBy::id('btn-auth-open'));
		$node->click();
	}

	function test_auth_pass_app($url = null)
	{

		self::$wd->get(sprintf('%s/account'
			, OPENTHC_TEST_ORIGIN
		));

		$node = self::$wd->findElement(WebDriverBy::cssSelector('[data-service-name=app]'));
		$node->click();

		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		$app_tab = $window_handles[ array_key_last($window_handles) ];
		self::$wd->switchTo()->window($app_tab);

		// Application Authorization
		$html = self::$wd->getPageSource();
		$node = self::$wd->findElement(WebDriverBy::cssSelector('#oauth2-authorize-permit'));
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = self::$wd->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		// Dashboard
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Dashboard ::/', $html);

		self::$wd->switchTo()->window($sso_tab);
		return self::$wd->getCurrentUrl();
	}

	function test_auth_pass_b2b()
	{
		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		self::$wd->switchTo()->window($sso_tab);

		$node = self::$wd->findElement(WebDriverBy::cssSelector('[data-service-name="b2b marketplace"]'));
		$node->click();

		$window_handles = self::$wd->getWindowHandles();
		$b2b_tab = $window_handles[ array_key_last($window_handles) ];
		self::$wd->switchTo()->window($b2b_tab);

		// Application Authorization
		$html = self::$wd->getPageSource();
		$node = self::$wd->findElement(WebDriverBy::cssSelector('#oauth2-authorize-permit'));
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = self::$wd->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		// Market Search
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Market/', $html);
		$this->assertMatchesRegularExpression('/Signed in as:/', $html);

		self::$wd->switchTo()->window($sso_tab);
		return self::$wd->getCurrentUrl();
	}

	function test_auth_pass_dir()
	{
		$this->assertTrue(true);
	}

	function test_auth_pass_pos()
	{
		$node = self::$wd->findElement(WebDriverBy::cssSelector('[data-service-name="retail pos"]'));
		$node->click();

		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		$pos_tab = $window_handles[ array_key_last($window_handles) ];
		self::$wd->switchTo()->window($pos_tab);

		// Application Authorization
		$html = self::$wd->getPageSource();
		$node = self::$wd->findElement(WebDriverBy::cssSelector('#oauth2-authorize-permit'));
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = self::$wd->findElement(WebDriverBy::id('oauth2-permit-continue'));
		$node->click();

		// Dashboard
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Dashboard/', $html);
		$this->assertMatchesRegularExpression('/POS/', $html);

		self::$wd->switchTo()->window($sso_tab);
		return self::$wd->getCurrentUrl();
	}

	function test_auth_pass_lab()
	{
		$this->assertTrue(true);
	}

}
