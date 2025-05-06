<?php
/**
 * UI Authentication Tests
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\B_Basic;

class B_Auth_Test extends \OpenTHC\SSO\Test\Browser\Base
{
	protected static $username;

	public static function setupBeforeClass(): void
	{
		parent::setupBeforeClass();
		self::$username = $_ENV['OPENTHC_TEST_CONTACT_A'];
	}

	// public function teardown() : void
	// {
		// Delete Contact?
	// }

	/**
	 *
	 */
	public function test_home_redirect()
	{
		// The Prime Site does a Meta-Refresh
		self::$wd->get($_ENV['OPENTHC_TEST_ORIGIN']);
		$src = self::$wd->getPageSource();
		$this->assertMatchesRegularExpression('/<meta http-equiv="refresh".+auth\/open/', $src);
		sleep(3); // Wait for refresh

		$url = self::$wd->getCurrentUrl();
		$this->assertMatchesRegularExpression('/sso\.openthc/', $url);

	}

	/**
	 * Test auth open when contact stat=200
	 */
	public function test_auth_open_success_one_company()
	{
		// self::$wd->manage()->deleteAllCookies();
		self::$wd->get(sprintf('%s/auth/open', $_ENV['OPENTHC_TEST_ORIGIN']));

		$element = $this->findElement('#username');
		$element->clear();
		$element->sendKeys($_ENV['OPENTHC_TEST_CONTACT_A']);

		$element = $this->findElement('#password');
		$element->clear();
		$element->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$btn = $this->findElement('#btn-auth-open');
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/account', $url);

		// Find a Company
		// $this->assertStringContainsString('/verify?_=', $url);
	}

	public function test_auth_open_success_pick_company()
	{
		// self::$wd->manage()->deleteAllCookies();
		self::$wd->get(sprintf('%s/auth/open', $_ENV['OPENTHC_TEST_ORIGIN']));

		$element = $this->findElement('#username');
		$element->clear();
		$element->sendKeys($_ENV['OPENTHC_TEST_CONTACT_C']);

		$element = $this->findElement('#password');
		$element->clear();
		$element->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$btn = $this->findElement('#btn-auth-open');
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/init?_=', $url);

		$sel = sprintf('#btn-company-%s', $_ENV['OPENTHC_TEST_CONTACT_C_COMPANY_A']);
		$btn = $this->findElement($sel);
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/account', $url);

	}

	/**
	 * Test Test Bad Account, Bad Password
	 */
	public function test_auth_open_fail_username()
	{
		self::$wd->get(sprintf('%s/auth/open', $_ENV['OPENTHC_TEST_ORIGIN']));

		$element = $this->findElement('#username');
		$element->clear();
		$element->sendKeys(sprintf('invalid-%s@openthc.example', _ulid()));

		$element = $this->findElement('#password');
		$element->clear();
		$element->sendKeys('WRONG_PASSWORD');

		$btn = $this->findElement('#btn-auth-open');
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=CAO-093', $url);
		$this->assertStringContainsString('Invalid Username or Password', self::$wd->getPageSource());

	}

	/**
	 * Test Test Good Account, Bad Password
	 */
	public function test_auth_open_fail_password()
	{
		self::$wd->get(sprintf('%s/auth/open', $_ENV['OPENTHC_TEST_ORIGIN']));

		$element = $this->findElement('#username');
		$element->clear();
		$element->sendKeys(self::$username);

		$element = $this->findElement('#password');
		$element->clear();
		$element->sendKeys('WRONG_PASSWORD');

		$btn = $this->findElement('#btn-auth-open');
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?e=CAO-153', $url);
		$this->assertStringContainsString('Invalid Username or Password', self::$wd->getPageSource());

	}

	/**
	 *
	 */
	public function test_auth_wellknown_reset()
	{
		//
		self::$wd->get(sprintf('%s/.well-known/change-password', $_ENV['OPENTHC_TEST_ORIGIN']));

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/open?a=password-reset', $url);

	}

	/**
	 * Test auth open when contact stat=100
	 * @todo needs to have the account actually added to test this
	 */
	public function x_test_auth_open_verify()
	{
		$host = parse_url($_ENV['OPENTHC_TEST_ORIGIN'], PHP_URL_HOST);

		$Contact = [];
		$Contact['id'] = _ulid();
		$Contact['username'] = strtolower(sprintf('test+%s@%s', $Contact['id'], $host));
		$Contact['password'] = password_hash($_POST['p0'], $_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);;
		$Contact['stat'] = 100;

		$dbc = $this->_dbc();
		$dbc->insert('auth_contact', $Contact);

		// self::$wd->manage()->deleteAllCookies();
		self::$wd->get(sprintf('%s/auth/open', $_ENV['OPENTHC_TEST_ORIGIN']));

		$element = $this->findElement('#username');
		$element->clear();
		$element->sendKeys($Contact['username']);

		$element = $this->findElement('#password');
		$element->clear();
		$element->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$btn = $this->findElement('#btn-auth-open');
		$btn->click();

		$url = self::$wd->getCurrentUrl();

		$this->assertStringContainsString('/done?e=CAO-144', $url);
		$this->assertStringContainsString('Account Pending', self::$wd->getPageSource());

		$dbc->query('DELETE FROM auth_contact WHERE id = :c0', [
			':c0' => $Contact['id'],
		]);
	}

	/**
	 * Test auth open when contact stat=410
	 */
	public function x_test_auth_open_gone()
	{
		// self::$wd->manage()->deleteAllCookies();
		self::$wd->get(sprintf('%s/auth/open', $_ENV['OPENTHC_TEST_ORIGIN']));

		$element = $this->findElement('#username');
		$element->clear();
		$element->sendKeys(self::$username);

		$element = $this->findElement('#password');
		$element->clear();
		$element->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PASSWORD']);

		$btn = $this->findElement('#btn-auth-open');
		$btn->click();

		$url = self::$wd->getCurrentUrl();
		$this->assertStringContainsString('/auth/init?_=', $url);
		$this->assertStringContainsString('Invalid Account', self::$wd->getPageSource());
	}
}
