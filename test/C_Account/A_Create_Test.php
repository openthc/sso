<?php
/**
 * Account Create Testing
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\C_Account;

class A_Create_Test extends \OpenTHC\SSO\Test\Base
{
	protected $type_expect = 'text/html';

	protected static $username;

	/**
	 *
	 */
	public static function setupBeforeClass(): void
	{
		parent::setupBeforeClass();
		$host = parse_url($_ENV['OPENTHC_TEST_ORIGIN'], PHP_URL_HOST);
		self::$username = strtolower(sprintf('test+%s@%s', _ulid(), $host));
	}

	/**
	 * Creates the Account and Sets the Password
	 */
	function test_account_create()
	{
		$c = $this->_ua();
		$res = $c->get('/account/create');
		$html = $this->assertValidResponse($res);
		syslog(LOG_DEBUG, "Create");

		// $this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Create Account', $html);
		// $this->assertMatchesRegularExpression('/input.+id="company\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $html);

		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'contact-next',
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => self::$username,
			'contact-phone' => '1234567890',
		];
		$res = $c->post('/account/create', [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);

		// Fails cause we already have an account
		$done_link = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/done\?e=CAC\-111/', $done_link);

		// Extract Data and Compare to the Recent Ticket
		// This Ticket is stored in Redis
		// Would have to $rdb->keys('/auth-ticket/*')->match('/contact-email.+self::$username')

		// It should be the most recent one
		// @todo make class param
		// $dbc = $this->_dbc();
		// $res = $dbc->fetchRow('SELECT * FROM auth_context_ticket ORDER BY created_at DESC limit 1');
		// var_dump($res);

		$this->assertMatchesRegularExpression('/^\/done\?e=CAC\-111.+t=/', $done_link);

		// Get Done Page
		$res = $c->get($done_link);
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('Account Confirmation', $html);
		$this->assertStringContainsString('Please check your email to confirm your account', $html);

		// Capture Email-Auth-Link from this response
		// It's only provided like this in TEST mode
		$auth_link = preg_match('/\?(.+)$/', $done_link, $m) ? $m[1] : '';
		$this->assertNotEmpty($auth_link);
		$args = __parse_str($auth_link);
		$this->assertIsArray($args);
		$this->assertArrayHasKey('t', $args);

		$res = $c->get(sprintf('/auth/once?_=%s', $args['t']));
		$this->assertValidResponse($res, 302);
		$link3 = $res->getHeaderLine('location');
		syslog(LOG_DEBUG, "GET-074 \$link3 = $link3");

		// $this->assertMatchesRegularExpression('/^\/done\?e=CAO\-073/', $link3);

		return $link3;

		// $res = $c->get($link3);
		// $html = $this->assertValidResponse($res);
		// $this->assertMatchesRegularExpression('/Account Confirmed/', $html);
		// $this->assertMatchesRegularExpression('/Set Password/', $html);

		// $url = preg_match('/href="(\/account\/password[^"]+)"/', $html, $m) ? $m[1] : '';
		// syslog(LOG_DEBUG, "GET-082 \$url = $url");
		// $res = $c->get($url);
		// $html = $this->assertValidResponse($res);
		// $this->assertMatchesRegularExpression('/Set Password/', $html);

		// POST to update password
		// syslog(LOG_DEBUG, "POST-088 \$url = $url");
		// $res = $c->post($url, [ 'form_params' => [
		// 	'a' => 'update',
		// 	'p0' => ,
		// 	'p1' => $_ENV['OPENTHC_TEST_CONTACT_PASSWORD'],
		// ]]);
		// $this->assertValidResponse($res, 302);
		// $url = $res->getHeaderLine('location');
		// $this->assertEquals('/auth/open?e=CAP-080', $url);

		// syslog(LOG_DEBUG, "GET-098 \$url = $url");
		// $res = $c->get($url);
		// $html = $this->assertValidResponse($res);
		// $this->assertMatchesRegularExpression('/Your Password has been updated, please sign-in to continue/', $html);

		// syslog(LOG_DEBUG, "DONE $url");

	}

	/**
	 * @depends test_account_create
	 */
	function test_account_verify($link3)
	{
		$this->assertNotEmpty($link3);
		$this->assertMatchesRegularExpression('/^\/account\/commit/', $link3);

		// Get /account/commit?
		$c = $this->_ua();
		$res = $c->get($link3);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');

		$this->assertMatchesRegularExpression('/^\/verify/', $url);
		$res = $c->get($url);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');

		// Prod has an additional UX step to manually verify your email is real
		// if (getenv('TEST_MODE') == 'prod') {
		// 	$this->assertMatchesRegularExpression('/^\/verify\/email/', $url);
		// 	// $res = $c->get($url);
		// 	$res = $c->post($url, [ 'form_params' => [ 'a' => 'verify-email-save' ] ]);
		// 	$this->assertValidResponse($res, 302);
		// 	$url = $res->getHeaderLine('location');
		// 	$this->assertMatchesRegularExpression('/^\/verify/', $url);
		// 	$res = $c->get($url);
		// 	$this->assertValidResponse($res, 302);
		// 	$url = $res->getHeaderLine('location');
		// }

		// Going to Password Form
		$this->assertMatchesRegularExpression('/^\/verify\/password.+/', $url);

		return $url;

	}

	/**
	 * @depends test_account_verify
	 */
	function test_verify_password($url0)
	{
		$c = $this->_ua();

		// Get It and go Next
		$res = $c->get($url0);
		$html = $this->assertValidResponse($res);
		// $this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Set Password', $html);

		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'update',
			'p0' => $_ENV['OPENTHC_TEST_CONTACT_PASSWORD'],
			'p1' => $_ENV['OPENTHC_TEST_CONTACT_PASSWORD'],
		];
		$res = $c->post($url0, [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify/', $url1);

		$res = $c->get($url1);
		$this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify\/location.+/', $url2);

		return $url2;
	}

	/**
	 * @depends test_verify_password
	 */
	function test_verify_location($url0)
	{
		$c = $this->_ua();

		$res = $c->get($url0);
		$html = $this->assertValidResponse($res);
		// $this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Verify Profile Location', $html);

		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'iso3166-1-save-next',
			'contact-iso3166-1' => 'US',
		];
		$res = $c->post($url0, [ 'form_params' => $arg ]);
		$html = $this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify\/location.+/', $url1);

		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'iso3166-2-save-next',
			'contact-iso3166-2' => 'US-WA',
		];
		$res = $c->post($url1, [ 'form_params' => $arg ]);
		$html = $this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify/', $url2);

		$res = $c->get($url2);
		$html = $this->assertValidResponse($res, 302);
		$url3 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify\/timezone.+/', $url3);

		return $url3;

	}

	/**
	 * @depends test_verify_location
	 */
	function test_verify_timezone($url0)
	{
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/^\/verify\/timezone\?_=.+/', $url0);

		$c = $this->_ua();

		// Time Zone
		$res = $c->get($url0);
		$html = $this->assertValidResponse($res);
		// $this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Verify Profile Timezone', $html);
		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'timezone-save-next',
			'contact-timezone' => 'America/Los_Angeles',
		];
		$res = $c->post($url0, [ 'form_params' => $arg ]);

		// Sends to /verify
		$this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify/', $url1);
		// Sends to /verify/phone (we hope)
		$res = $c->get($url1);
		$this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');

		return $url2;

	}

	/**
	 * @depends test_verify_timezone
	 */
	// function test_verify_phone($url0)
	// {
	// 	$this->assertNotEmpty($url0);
	// 	$this->assertMatchesRegularExpression('/^\/verify\/phone\?_=.+/', $url0);

	// 	$c = $this->_ua();

	// 	// Phone Number
	// 	$res = $c->get($url0);
	// 	$html = $this->assertValidResponse($res);
	// 	$this->assertStringContainsString('TEST MODE', $html);
	// 	$this->assertStringContainsString('Verify Phone', $html);
	// 	$this->assertStringContainsString('name="contact-phone"', $html);
	// 	$this->assertMatchesRegularExpression('/name="a".*?type="submit".*?value="phone\-verify\-send"/', $html);

	// 	// // POST Phone Verify
	// 	$res = $c->post($url0, [ 'form_params' => [
	// 		'CSRF' => $this->getCSRF($html),
	// 		'a' => 'phone-verify-send',
	// 		'contact-phone' => '+18559769333',
	// 	]]);
	// 	$this->assertValidResponse($res, 302);

	// 	// Good, Bounce to Phone Again
	// 	$url1 = $res->getHeaderLine('location');
	// 	$this->assertMatchesRegularExpression('/^\/verify\/phone\?_=.+&t=.+/', $url1);
	// 	$phone_code = preg_match('/t=(\w+)/', $url1, $m) ? $m[1] : null;
	// 	$this->assertNotEmpty($phone_code);
	// 	$this->assertMatchesRegularExpression('/^\w{6}$/', $phone_code);

	// 	$res = $c->get($url1);
	// 	$html = $this->assertValidResponse($res);
	// 	$this->assertStringContainsString('TEST MODE', $html);
	// 	$this->assertStringContainsString('Verify Phone', $html);
	// 	$this->assertStringContainsString('name="contact-phone"', $html);
	// 	$this->assertMatchesRegularExpression('/name="a".*?type="submit".*?value="phone-verify-send"/', $html);
	// 	$this->assertStringContainsString('name="phone-verify-code"', $html);
	// 	$this->assertMatchesRegularExpression('/name="a".*?type="submit".*?value="phone\-verify\-save"/', $html);

	// 	// // POST to Verify Code, 302 => Verify, 302 => Password
	// 	$res = $c->post($url1, [ 'form_params' => [
	// 		'CSRF' => $this->getCSRF($html),
	// 		'a' => 'phone-verify-save',
	// 		'phone-verify-code' => $phone_code,
	// 	]]);
	// 	$this->assertValidResponse($res, 302);
	// 	$url2 = $res->getHeaderLine('location');

	// 	$this->assertMatchesRegularExpression('/^\/verify\?_=.+/', $url2);

	// 	// Should be Bouncing us to Password
	// 	$res = $c->get($url2);
	// 	$url3 = $res->getHeaderLine('location');

	// 	return $url3;

	// }

	/**
	 * @depends test_verify_timezone
	 */
	function test_verify_company($url0)
	{
		$this->markTestSkipped('We do not think this is a feature that will stay around.');

		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/^\/verify\/company\?_=.+/', $url0);

		$c = $this->_ua();

		// Get Company Page
		$res = $c->get($url0);
		$html = $this->assertValidResponse($res);
		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'company-skip',
		];

		$res = $c->post($url0, [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify\?_=.+/', $url1);

		$res = $c->get($url1);
		$this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');

		return $url2;

	}

	/**
	 * @depends test_verify_company
	 */
	function test_verify_license($url0)
	{
		$this->assertNotEmpty($url0);
		return $url0;

		if (getenv('TEST_MODE') == 'prod') {
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/^\/verify\/license\?_=.+/', $url0);

		$c = $this->_ua();

		// Get License Page
		$res = $c->get($url0);
		$html = $this->assertValidResponse($res);
		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'license-skip',
		];

		$res = $c->post($url0, [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify\?_=.+/', $url1);

		$res = $c->get($url1);
		$this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');

		} else {
			// var_dump('Verify Company not implemented on dev?');
			$url2 = $url0;
		}
		return $url2;

	}

	/**
	 * @depends test_verify_company
	 */
	function test_account_create_done($url0)
	{

		$this->assertNotEmpty($url0);

		if (getenv('TEST_MODE') == 'prod') {
			$c = $this->_ua();
			$res = $c->post($url0, [ 'form_params' => [ 'a' => 'license-verify-skip' ] ]);
			$this->assertValidResponse($res, 302);
			$url1 = $res->getHeaderLine('location');
			$res = $c->get($url1);
			$this->assertValidResponse($res, 302);
			$url2 = $res->getHeaderLine('location');
			$url0 = $url2;
			$this->assertStringContainsString('/done?e=CVM-130', $url0); // ???
		} else {
		$this->assertStringContainsString('/done?e=CVM-119', $url0); // Prompt to Sign-In
		// $this->assertStringContainsString('/done?e=CVM-130', $url0); // Prompt to Wait for Activation
		// $this->assertMatchesRegularExpression('/^\/auth\/init\?_=.+/', $url0);
		}

		// Sign In and Get aSome Message?
		$c = $this->_ua();
		$res = $c->get($url0);
		$html = $this->assertValidResponse($res, 200);

		$this->assertStringContainsString('Verification Complete', $html);
		// $this->assertStringContainsString('Account Pending Activation', $html);

	}

	/**
	 * Duplicate Email should be Rejected
	 * @note Implicitly dependant on the Email Verification step
	 */
	function test_account_create_dupe_email()
	{
		$c = $this->_ua();
		$res = $c->get('/account/create');
		$html = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/Create Account/', $html);
		// $this->assertMatchesRegularExpression('/input.+id="company\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $html);

		$res = $c->post('/account/create', [ 'form_params' => [
			'CSRF' => $this->getCSRF($html),
			'a' => 'contact-next',
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => self::$username,
		]]);
		$this->assertValidResponse($res, 302);

		$url1 = $res->getHeaderLine('location');
		// $this->assertEquals('/done?e=CAC-083', $url1); // Created not Active
		// if (getenv('TEST_MODE') == 'prod') {
		// 	$this->assertEquals('/done?e=CAC-083', $url1); // Created and not Active
		// } else {
		$this->assertEquals('/done?e=CAC-086', $url1); // Created and Active
		// }

		$res = $c->get($url1);
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('You have already created an account, sign in to that one', $html);

	}

	/**
	 *
	 */
	function test_account_create_fail_email()
	{
		$c = $this->_ua();

		// Create0/GET
		$res = $c->get('/account/create');
		$html = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/Create Account/', $html);
		// $this->assertMatchesRegularExpression('/input.+id="company\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $html);

		// Create1/POST
		$res = $c->post('/account/create', [ 'form_params' => [
			'CSRF' => $this->getCSRF($html),
			'a' => 'contact-next',
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => 'invalid.email-typeA',
		]]);
		$this->assertValidResponse($res, 302);
		$url1= $res->getHeaderLine('location');
		$this->assertEquals('/account/create?e=CAC-035', $url1);

		$res = $c->get($url1);
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('Invalid Email Address', $html);

	}

	/**
	 *
	 */
	function test_account_password_reset()
	{
		$c = $this->_ua();

		// GET
		$res = $c->get('/auth/open');
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('/auth/open?a=password-reset', $html);

		// GET
		$res = $c->get('/auth/open?a=password-reset');
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('<input class="form-control" id="username" inputmode="email" name="username"', $html);
		$this->assertStringContainsString('<button class="btn btn-primary" id="btn-password-reset" name="a" type="submit" value="password-reset-request">Request Password Reset</button>', $html);

		// POST
		$res = $c->post('/auth/open?a=password-reset', [ 'form_params' => [
			'CSRF' => $this->getCSRF($html),
			'a' => 'password-reset-request',
			'username' => self::$username,
		]]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		if (getenv('TEST_MODE') == 'prod') {
			$this->assertMatchesRegularExpression('/^\/done\?e=CAO\-200/', $url);
		} else {
			$this->assertMatchesRegularExpression('/^\/done\?e=CAO\-200&t=.+/', $url);
		}

		$res = $c->get($url);
		$html = $this->assertValidResponse($res);

		// @todo Verify Contents of the Done Page
		$this->assertStringContainsString('Check Your Inbox', $html);

		if (getenv('TEST_MODE') == 'prod') {
			$tok = trim(fgets(STDIN));
			var_dump("Token: $tok");
		} else {
		$tok = preg_match('/t=(.+)$/', $url, $m) ? $m[1] : '';
		}
		$url1 = sprintf('%s/auth/once?_=%s', $_ENV['OPENTHC_TEST_ORIGIN'], $tok);
		$this->assertNotEmpty($url1);

		// Follow to Password Reset Page?
		$res = $c->get($url1);
		$html = $this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');
		$res = $c->get($url2);
		$html = $this->assertValidResponse($res, 200);

		$this->assertStringContainsString('Save Password', $html);
		$this->assertMatchesRegularExpression('/(Confirm|Save) Password/', $html);


	}

}
