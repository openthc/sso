<?php
/**
 * Base Class for API Testing
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

		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Create Account', $html);
		$this->assertMatchesRegularExpression('/input.+id="company\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $html);

		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'contact-next',
			'company-name' => sprintf('Test License %06x', $this->_pid),
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			'contact-phone' => '1234567890',
		]]);
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
		// 	'p0' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
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
		$html = $this->assertValidResponse($res);

		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Account Verification', $html);
		$this->assertStringContainsString('name="contact-phone"', $html);
		$this->assertStringContainsString('name="a" type="submit" value="phone-verify-send"', $html);

		// POST Phone Verify
		$res = $c->post($link3, [ 'form_params' => [
			'a' => 'phone-verify-send',
			'contact-phone' => '+18559769333',
		]]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/account\/verify\?_=.+&c=.+/', $url);
		$phone_code = preg_match('/c=(\w+)/', $url, $m) ? $m[1] : null;
		$this->assertNotEmpty($phone_code);
		$this->assertMatchesRegularExpression('/^\w{6}$/', $phone_code);

		$res = $c->get($url);
		$html = $this->assertValidResponse($res);
		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Account Verification', $html);
		$this->assertStringContainsString('name="contact-phone"', $html);
		$this->assertStringContainsString('name="a" type="submit" value="phone-verify-send"', $html);

		$this->assertStringContainsString('name="phone-verify-code"', $html);
		$this->assertStringContainsString('name="a" type="submit" value="phone-verify-save"', $html);

		// POST to Verify Code, 302 => Verify, 302 => Password
		$res = $c->post($url, [ 'form_params' => [
			'a' => 'phone-verify-save',
			'phone-verify-code' => $phone_code,
		]]);
		$this->assertValidResponse($res, 302);

		$url = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/account\/verify\?_=.+/', $url);

		// Should be Bouncing us to Password
		$res = $c->get($url);
		$url = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/account\/password\?_=.+/', $url);

		return $url;

	}

	/**
	 * @depends test_account_verify
	 */
	function test_account_password($url)
	{
		$this->assertNotEmpty($url);
		$this->assertMatchesRegularExpression('/^\/account\/password\?_=.+/', $url);

		$c = $this->_ua();
		$res = $c->get($url);
		$html = $this->assertValidResponse($res);

		$this->assertStringContainsString('TEST MODE', $html);
		$this->assertStringContainsString('Set Password', $html);
		$this->assertStringContainsString('id="password0" type="password" name="p0"', $html);
		$this->assertStringContainsString('id="password1" type="password" name="p1"', $html);
		$this->assertStringContainsString('name="a" type="submit" value="update"', $html);

		$res = $c->post($url, [ 'form_params' => [
			'a' => 'update',
			'p0' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'), // '1TestPass!',
			'p1' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'), // '1TestPass!',
		]]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/auth\/open/', $url);

	}

	/**
	 * Complete the Verification
	 *
	 */
	function x_test_account_auth_verify()
	{

		$c = $this->_ua();
		$res = $c->get(sprintf('/auth/open?_t=%s',  getenv('OPENTHC_TEST_HASH')));
		$res = $this->assertValidResponse($res);
		$this->assertMatchesRegularExpression('/TEST MODE/', $this->raw);

		$res = $c->post('/auth/open', [ 'form_params' => [
			'a' => 'sign in',
			'username' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			'password' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
		]]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		// var_dump($url);
		$this->assertMatchesRegularExpression('/^\/auth\/init\?_=.+/', $url);

		// Now Init
		$res = $c->get($url);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/account\/verify\?r=%2F.+&_=.+/', $url);

		// Get the Verify Page
		$res = $c->get($url);
		$this->assertValidResponse($res);
		$this->assertMatchesRegularExpression('/Account Verification/', $this->raw);

		// Submit Phone Verification
		$res = $c->post($url, [ 'form_params' => [
			'a' => 'phone-verify-send',
			'contact-phone' => getenv('OPENTHC_TEST_CONTACT_PHONE'),
		]]);
		$this->assertValidResponse($res, 302);

		// Redirects to Self to Enter the Code
		$url = $res->getHeaderLine('location');
		// var_dump($url);
		$this->assertMatchesRegularExpression('/^\/account\/verify\?_=.+c=.+/', $url);
		$res = $c->get($url);
		file_put_contents('../test_account_auth_verify-147.html', $this->raw);

		$code = preg_match('/c=(\w+)/', $url, $m) ? $m[1] : null;
		$this->assertNotEmpty($code);

		// Submit This Verification Code
		$res = $c->post($url, [ 'form_params' => [
			'a' => 'phone-verify-save',
			'phone-verify-code' => $code,
			'contact-phone' => getenv('OPENTHC_TEST_CONTACT_PHONE'),
		]]);
		$this->assertValidResponse($res, 302);
		// file_put_contents('../test_account_auth_verify-147.html', $this->raw);

	}

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
			'company-name' => sprintf('Test Company %06x', $this->_pid),
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			'contact-phone' => '1234567890',
		]]);
		$this->assertValidResponse($res, 302);

		$l = $res->getHeaderLine('location');
		$this->assertEquals('/done?e=CAC-065', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);

	}

	function test_account_create_fail_email()
	{
		$c = $this->_ua();

		// Create0/GET
		$res = $c->get('/account/create');
		$res = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/Create Account/', $res);
		$this->assertMatchesRegularExpression('/input.+id="company\-name"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="contact\-name"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="contact\-email"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="contact\-phone"/', $res);

		// Create1/POST
		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'contact-next',
			'company-name' => sprintf('Test Company %06x', $this->_pid),
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'contact-email' => 'invalid.email-typeA',
			'contact-phone' => '1234567890',
		]]);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/account/create?e=CAC-035', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);

	}

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
		var_dump($url);

		$this->assertNotEmpty($url);

	}

}
