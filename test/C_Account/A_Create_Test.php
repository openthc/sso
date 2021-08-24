<?php
/**
 * Account Create Testing
 */

namespace Test\C_Account;

class A_Create_Test extends \Test\Base_Case
{
	// private $account_username =
	private $link_verify;

	/**
	 * Creates the Account and Sets the Password
	 */
	function test_account_create()
	{
		$test_secret = getenv('OPENTHC_TEST_HASH');
		$this->assertNotEmpty($test_secret);

		$c = $this->_ua();
		$res = $c->get('/account/create?_t=' . $test_secret);
		$html = $this->assertValidResponse($res);
		syslog(LOG_DEBUG, "Create");

		// $this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Create Account', $html);
		$this->assertMatchesRegularExpression('/input.+id="company\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $html);

		$arg = [
			'CSRF' => (preg_match('/name="CSRF" type="hidden" value="([^"]+)"/', $html, $m) ? $m[1] : ''),
			'a' => 'contact-next',
			// 'company-name' => sprintf('Test License %06x', $this->_pid),
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			// 'contact-phone' => '1234567890',
		];
		$res = $c->post('/account/create', [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);

		$done_link = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/done\?e=CAC\-111/', $done_link);
		$this->assertMatchesRegularExpression('/^\/done\?e=CAC\-111.+r=/', $done_link); // Has Test Link

		// Get Done Page
		syslog(LOG_DEBUG, "GET-047 \$done_link = $done_link");
		$res = $c->get($done_link);
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('Account Confirmation', $html);
		$this->assertStringContainsString('Please check your email to confirm your account', $html);

		// Capture Email-Auth-Link from this response
		// It's only provided like this in TEST mode
		$auth_link = preg_match('/\?(.+)$/', $done_link, $m) ? $m[1] : '';
		$this->assertNotEmpty($auth_link);
		$auth_link = __parse_str($auth_link);
		$this->assertIsArray($auth_link);
		$this->assertArrayHasKey('r', $auth_link);
		$auth_link = $auth_link['r'];

		syslog(LOG_DEBUG, "GET-062 \$auth_link = $auth_link");
		$res = $c->get($auth_link);
		$this->assertValidResponse($res, 302);
		$link3 = $res->getHeaderLine('location');
		// $this->assertMatchesRegularExpression('/^\/done\?e=CAO\-073/', $link3);

		return $link3;

		// syslog(LOG_DEBUG, "GET-074 \$link3 = $link3");
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
		// 	'p1' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
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

		$c = $this->_ua();
		$res = $c->get($link3);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
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
		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Set Password', $html);

		$arg = [
			'a' => 'update',
			'p0' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
			'p1' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
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
		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Verify Profile Location', $html);

		$arg = [
			'a' => 'iso3166-1-save-next',
			'contact-country' => 'US',
		];
		$res = $c->post($url0, [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify\/location.+/', $url1);

		$arg = [
			'a' => 'iso3166-2-save-next',
			'contact-iso3166-2' => 'US-WA',
		];
		$res = $c->post($url1, [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify/', $url2);

		$res = $c->get($url2);
		$this->assertValidResponse($res, 302);
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
		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Verify Profile Timezone', $html);
		$arg = [
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
	function test_verify_phone($url0)
	{
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/^\/verify\/phone\?_=.+/', $url0);

		$c = $this->_ua();

		// Phone Number
		$res = $c->get($url0);
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Verify Phone', $html);
		$this->assertStringContainsString('name="contact-phone"', $html);
		$this->assertMatchesRegularExpression('/name="a".*?type="submit".*?value="phone\-verify\-send"/', $html);

		// // POST Phone Verify
		$res = $c->post($url0, [ 'form_params' => [
			'a' => 'phone-verify-send',
			'contact-phone' => '+18559769333',
		]]);
		$this->assertValidResponse($res, 302);

		// Good, Bounce to Phone Again
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/verify\/phone\?_=.+&c=.+/', $url1);
		$phone_code = preg_match('/c=(\w+)/', $url1, $m) ? $m[1] : null;
		$this->assertNotEmpty($phone_code);
		$this->assertMatchesRegularExpression('/^\w{6}$/', $phone_code);

		$res = $c->get($url1);
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Verify Phone', $html);
		$this->assertStringContainsString('name="contact-phone"', $html);
		$this->assertMatchesRegularExpression('/name="a".*?type="submit".*?value="phone-verify-send"/', $html);
		$this->assertStringContainsString('name="phone-verify-code"', $html);
		$this->assertMatchesRegularExpression('/name="a".*?type="submit".*?value="phone\-verify\-save"/', $html);

		// // POST to Verify Code, 302 => Verify, 302 => Password
		$res = $c->post($url1, [ 'form_params' => [
			'a' => 'phone-verify-save',
			'phone-verify-code' => $phone_code,
		]]);
		$this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');

		$this->assertMatchesRegularExpression('/^\/verify\?_=.+/', $url2);

		// // Should be Bouncing us to Password
		$res = $c->get($url2);
		$url3 = $res->getHeaderLine('location');

		return $url3;

	}

	/**
	 * @depends test_verify_phone
	 */
	function test_verify_company($url0)
	{
		$this->assertNotEmpty($url0);
		$this->assertMatchesRegularExpression('/^\/verify\/company\?_=.+/', $url0);

		$c = $this->_ua();

		// Get Company Page
		$res = $c->get($url0);
		$html = $this->assertValidResponse($res);
		$arg = [
			'a' => 'company-skip',
		];

		$res = $c->post($url0, [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');

		return $url1;

	}

	/**
	 * @depends test_account_verify
	 */
	// function test_account_password($url)
	// {
	// 	$this->assertNotEmpty($url);
	// 	$this->assertMatchesRegularExpression('/^\/account\/password\?_=.+/', $url);

	// 	$c = $this->_ua();
	// 	$res = $c->get($url);
	// 	$html = $this->assertValidResponse($res);

	// 	$this->assertStringContainsString('TEST MODE', $html);
	// 	$this->assertStringContainsString('Set Password', $html);
	// 	$this->assertStringContainsString('id="password0" type="password" name="p0"', $html);
	// 	$this->assertStringContainsString('id="password1" type="password" name="p1"', $html);
	// 	$this->assertStringContainsString('name="a" type="submit" value="update"', $html);

	// 	$res = $c->post($url, [ 'form_params' => [
	// 		'a' => 'update',
	// 		'p0' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'), // '1TestPass!',
	// 		'p1' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'), // '1TestPass!',
	// 	]]);
	// 	$this->assertValidResponse($res, 302);
	// 	$url = $res->getHeaderLine('location');
	// 	$this->assertMatchesRegularExpression('/^\/auth\/open/', $url);

	// }

	/**
	 * Duplicate Email should be Rejected
	 */
	function test_account_create_dupe_email()
	{
		$c = $this->_ua();
		$res = $c->get('/account/create');
		$res = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/Create Account/', $res);
		$this->assertMatchesRegularExpression('/input.+id="company\-name"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $res);

		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'contact-next',
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
		]]);
		$this->assertValidResponse($res, 302);

		$url1 = $res->getHeaderLine('location');
		$this->assertEquals('/account/create?e=CAC-049', $url1);

		$res = $c->get($url1);
		$this->assertValidResponse($res);

	}

	function test_account_create_fail_email()
	{
		$c = $this->_ua();

		// Create0/GET
		$res = $c->get('/account/create');
		$html = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/Create Account/', $html);
		$this->assertMatchesRegularExpression('/input.+id="company\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $html);

		// Create1/POST
		$res = $c->post('/account/create', [ 'form_params' => [
			'CSRF' => (preg_match('/name="CSRF" type="hidden" value="([^"]+)"/', $html, $m) ? $m[1] : ''),
			'a' => 'contact-next',
			// 'company-name' => sprintf('Test Company %06x', $this->_pid),
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => 'invalid.email-typeA',
			// 'contact-phone' => '1234567890',
		]]);
		$this->assertValidResponse($res, 302);
		$url1= $res->getHeaderLine('location');
		$this->assertEquals('/account/create?e=CAC-035', $url1);

		$res = $c->get($url1);
		$this->assertValidResponse($res);

	}

	/**
	 *
	 */
	function test_account_password_reset()
	{
		$c = $this->_ua();

		// GET
		$res = $c->get('/auth/open');
		$res = $this->assertValidResponse($res);
		$this->assertStringContainsString('/auth/open?a=password-reset', $res);

		// GET
		$res = $c->get('/auth/open?a=password-reset');
		$this->assertValidResponse($res);
		$this->assertStringContainsString('<input class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" value="">', $this->raw);
		$this->assertStringContainsString('<button class="btn btn-success" name="a" type="submit" value="password-reset-request">Request Password Reset</button>', $this->raw);

		// POST
		$res = $c->post('/auth/open?a=password-reset', [ 'form_params' => [
			'a' => 'password-reset-request',
			'username' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
		]]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		// $this->assertEquals('/done?e=CAO-100&l=200&s=t', $url); // LIVE
		$this->assertMatchesRegularExpression('/^\/done\?e=CAO\-100&l=200&r=.+/', $url);

		$res = $c->get($url);
		$this->assertValidResponse($res);

		// @todo Verify Contents of the Done Page
		$this->assertStringContainsString('Check Your Inbox', $this->raw);
		$this->assertGreaterThan(1024, strlen($this->raw));

		$url = preg_match('/r=(.+)$/', $url, $m) ? $m[1] : '';

		$this->assertNotEmpty($url);

	}

}
