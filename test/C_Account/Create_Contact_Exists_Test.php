<?php
/**
 Contact already exists in main.contact by email
 Channel record does NOT exist for the given email.
 Sign-Up sends email notification
 Sign-Up routes to Verify which fails (and should work)
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\C_Account;

use Facebook\WebDriver\WebDriverBy;

class Create_Contact_Exists_Test extends \OpenTHC\SSO\Test\Browser\Base
{
	public static $Contact = [
		'id' => '01JH185V65H4000TEST2025007',
		// 'flag' => '',
		'name' => 'Create Contact Exists with Invalid Status',
		'email' => 'test+2025-007@openthc.com',
		'phone' => '',
		// 'deleted_at' => '',
		'name_first' => '',
		'name_last' => '',
		'hash' => '',
		'stat' => 400,
		// 'created_at' => '',
		// 'updated_at' => '',
		'tz' => '',
		'iso3166' => '',
		'origin' => '',
	];
	public static $Channel = [
		'id' => '01JH185V65H4000TEST2025007',
		'data' => 'test+2025-007@openthc.com',
		'type' => 'email',
		// 'stat' => 100,
	];

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();

		$dbc = self::_dbc('main');
		$C0 = $dbc->fetchRow('SELECT * FROM contact WHERE id = :pk', [':pk' => self::$Contact['id']]);

		// Requirement: Contact already exists in main.contact by email, contact.stat set to Invalid
		if (empty($C0['id'])) {
			$dbc->insert('contact', self::$Contact);
		}
		// Requirement: Channel record does NOT exist for the given email.
		$Ch0 = $dbc->fetchRow('SELECT * FROM channel WHERE type = :t0 AND data = :e0', [
			':t0' => 'email',
			':e0' => self::$Contact['email'],
		]);
		if ($Ch['id']) {
			$dbc->delete('channel', ['id' => $Ch0['id']]);
		}
	}

	public static function tearDownAfterClass() : void
	{
		parent::tearDownAfterClass();
		$dbc = self::_dbc('main');
		$dbc->delete('contact', ['id' => self::$Contact['id']]);
		$dbc->delete('contact_channel', ['channel_id' => self::$Channel['id'], 'contact_id' => self::$Contact['id']]);
		$dbc->delete('channel', ['id' => self::$Channel['id']]);
		$dbc->delete('channel', ['type' => 'email', 'data' => self::$Channel['email']]);
	}

	function test_create_contact_exists()
	{
		self::$wd->get(sprintf('%s/account/create'
			, $_ENV['OPENTHC_TEST_ORIGIN']
		));

		$node = $this->findElement('#alert-test-mode');
		$txt = $node->getText();
		$this->assertEquals('TEST MODE', $txt, 'Apache2 Environment missing variable: SetEnv OPENTHC_TEST "TEST"');

		$node = $this->findElement('#contact-name');
		$node->sendKeys(self::$Contact['email']);

		$node = $this->findElement('#contact-email');
		$node->sendKeys(self::$Contact['email']);

		$node = $this->findElement('#contact-phone');
		$node->sendKeys($_ENV['OPENTHC_TEST_CONTACT_PHONE']);

		$node = $this->findElement('#btn-account-create');
		$node->click();

		// Requirement: Sign-Up sends email notification
		$node = $this->findElement('#alert-test-link');
		$a = $node->findElement(WebDriverBy::cssSelector('a'));
		$url1 = $a->getAttribute('href');
		$this->assertNotEmpty($url1, 'Apache2 Environment missing variable: SetEnv OPENTHC_TEST "TEST"');

		// Requirement: Sign-Up routes to Verify which fails (and should work)
		self::$wd->get($_ENV['OPENTHC_TEST_ORIGIN'] . $url1);
		// $this->assertStringNotContainsString('CAV-037', self::$wd->getPageSource());
	}

	function test_api_contact_search()
	{
		// Clean up last run
		self::tearDownAfterClass();
		self::setUpBeforeClass();

		$dbc = self::_dbc('main');
		$sso = new \OpenTHC\Service\OpenTHC('sso');

		var_dump(self::$Contact['email']);
		$url = sprintf('/api/contact?q=%s', rawurlencode(self::$Contact['email']));
		$res = $sso->get($url);

		$this->assertEquals(404, $res['code']);
		$this->assertEquals(self::$Contact['email'], $res['data']['email']);

		$Channel = $dbc->fetchRow('SELECT * FROM channel WHERE type = :t0 AND data = :d0', [
			':t0' => 'email',
			':d0' => self::$Contact['email'],
		]);
		$this->assertEquals(100, $Channel['stat']);
	}
}
