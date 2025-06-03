<?php
/**
 * Test Default Service Connections in SSO
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\G_oAuth2;

class B_Service_UI_Test extends \OpenTHC\SSO\Test\Browser\Base
{
	function test_sign_in() : void
	{
		if (empty(self::$wd)) {
			$this->markTestSkipped('No Webdriver');
		}

		self::$wd->get(sprintf('%s/auth/open'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$username = $_ENV['OPENTHC_TEST_CONTACT_B'];
		$node = $this->findElement('#username');
		$node->sendKeys($username);

		$password = $_ENV['OPENTHC_TEST_CONTACT_PASSWORD'];
		$node = $this->findElement('#password');
		$node->sendKeys($password);

		$node = $this->findElement('#btn-auth-open');
		$node->click();

		$this->assertTrue(true);
		$url = self::$wd->getCurrentUrl();
		// var_dump($url);
		$this->assertMatchesRegularExpression('/http.+sso\..+\/account/', $url);
	}

	/**
	 * @depends test_sign_in
	 */
	function test_auth_pass_app()
	{
		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		self::$wd->switchTo()->window($sso_tab);

		self::$wd->get(sprintf('%s/account'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$node = $this->findElement('[data-service-name="app"]');
		$node->click();

		$window_handles = self::$wd->getWindowHandles();
		$app_tab = $window_handles[ array_key_last($window_handles) ];
		self::$wd->switchTo()->window($app_tab);

		// Opening in a new window causes some test flaky-ness /mbw 2025-070
		sleep(2);
		/*
		// Application Authorization
		sleep(1);
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$this->assertStringContainsString('oauth2-authorize-permit', $html);
		$node = $this->findElement('#oauth2-authorize-permit');
		$node->click();

		// Application Permitted
		sleep(1);
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = $this->findElement('#oauth2-permit-continue');
		$node->click();
		*/

		// Dashboard
		// It will be Setup, not Dashboard Depending on Account
		// self::$wd->get(sprintf('%s/dashboard', $_ENV['OPENTHC_TEST_ORIGIN']));
		$url = self::$wd->getCurrentUrl();
		// var_dump($url);
		$this->assertMatchesRegularExpression('/http.+app\./', $url);

		// $html = self::$wd->getPageSource();
		// $this->assertMatchesRegularExpression('/Dashboard/', $html);

		self::$wd->close();
	}

	/**
	 * @depends test_sign_in
	 */
	function test_auth_pass_b2b()
	{
		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		self::$wd->switchTo()->window($sso_tab);

		$node = $this->findElement('[data-service-name="b2b marketplace"]');
		$node->click();

		$window_handles = self::$wd->getWindowHandles();
		$b2b_tab = $window_handles[ array_key_last($window_handles) ];
		self::$wd->switchTo()->window($b2b_tab);

		sleep(1);
		/*
		// Application Authorization
		sleep(1);
		$html = self::$wd->getPageSource();
		$node = $this->findElement('#oauth2-authorize-permit');
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		sleep(1);
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = $this->findElement('#oauth2-permit-continue');
		$node->click();
		*/

		// Market Search
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Market/', $html);
		//$this->assertMatchesRegularExpression('/Signed in as:/', $html);

		self::$wd->close();
	}

	/**
	 * @depends test_sign_in
	 */
	function test_auth_pass_dir()
	{
		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		self::$wd->switchTo()->window($sso_tab);

		$node = $this->findElement('[data-service-name="directory"]');
		$node->click();

		$window_handles = self::$wd->getWindowHandles();
		$dir_tab = $window_handles[ array_key_last($window_handles) ];
		self::$wd->switchTo()->window($dir_tab);

		$this->assertTrue(true);

		sleep(1);
		/*
		// Application Authorization
		sleep(1);
		$html = self::$wd->getPageSource();
		$node = $this->findElement('#oauth2-authorize-permit');
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		sleep(1);
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = $this->findElement('#oauth2-permit-continue');
		$node->click();
		*/

		// Directory
		$html = self::$wd->getPageSource();
		//$this->assertMatchesRegularExpression('/Directory :: Search/', $html);

		self::$wd->close();

	}

	/**
	 * @depends test_sign_in
	 */
	function test_auth_pass_pos()
	{
		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		self::$wd->switchTo()->window($sso_tab);

		$node = $this->findElement('[data-service-name="retail pos"]');
		$node->click();

		$window_handles = self::$wd->getWindowHandles();
		$pos_tab = $window_handles[ array_key_last($window_handles) ];
		self::$wd->switchTo()->window($pos_tab);

		sleep(1);
		/*
		// Application Authorization
		sleep(1);
		$html = self::$wd->getPageSource();
		$node = $this->findElement('#oauth2-authorize-permit');
		$this->assertMatchesRegularExpression('/Application Authorization/', $html);
		$node->click();

		// Application Permitted
		sleep(1);
		$html = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/Application Permitted/', $html);
		$node = $this->findElement('#oauth2-permit-continue');
		$node->click();
		*/

		// Dashboard
		$html = self::$wd->getPageSource();
		//$this->assertMatchesRegularExpression('/Dashboard/', $html);
		$this->assertMatchesRegularExpression('/POS/', $html);

		self::$wd->close();
		// self::$wd->switchTo()->window($sso_tab);
		// return self::$wd->getCurrentUrl();
	}

	/**
	 * @depends test_sign_in
	 */
	function x_test_auth_pass_lab()
	{
		$window_handles = self::$wd->getWindowHandles();
		$sso_tab = $window_handles[0];
		self::$wd->switchTo()->window($sso_tab);

		$node = $this->findElement('[data-service-name="laboratory portal"]');
		$node->click();

		$this->assertTrue(true);

	}

}
