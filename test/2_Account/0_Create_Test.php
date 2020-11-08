<?php
/**
 * Base Class for API Testing
 */

namespace Test\Account;

class Create_Test extends \Test\Base_Case
{
	function test_account_create_pass()
	{
		$test_secret = \OpenTHC\Config::get('application_test.secret');
		$this->assertNotEmpty($test_secret);

		$c = $this->_ua();
		$res = $c->get('/account/create?_t=' . $test_secret);
		$res = $this->assertValidResponse($res);

		$html = $res;

		$this->assertRegExp('/TEST MODE/', $html);
		$this->assertRegExp('/Create Account/', $html);
		$this->assertRegExp('/input.+id="license\-name"/', $html);
		$this->assertRegExp('/input.+id="contact\-name"/', $html);
		$this->assertRegExp('/input.+id="contact\-email"/', $html);
		$this->assertRegExp('/input.+id="contact\-phone"/', $html);

		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'contact-next',
			'license-name' => sprintf('Test License %06x', $this->_pid),
			'license-id' => '',
			'company-id' => '',
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'email' => sprintf('test+%06x@openthc.com', $this->_pid),
			'phone' => '1234567890',
		]]);
		$this->assertValidResponse($res, 302);

		$l = $res->getHeaderLine('location');
		$this->assertRegExp('/^\/done\?e=cac111/', $l);

		$res = $res->getBody()->getContents();

		$res = $c->get($l);
		$this->assertValidResponse($res);

		$html = $this->raw;
		$this->assertRegExp('/Account Confirmation/', $html);
		$this->assertRegExp('/Please check your email to confirm your account/', $html);

	}

	/**
	 * Duplicate Email should be Rejected
	 */
	function test_account_create_dupe_email()
	{
		$c = $this->_ua();
		$res = $c->get('/account/create');
		$res = $this->assertValidResponse($res);

		$this->assertRegExp('/Create Account/', $res);
		$this->assertRegExp('/input.+id="license\-name"/', $res);
		$this->assertRegExp('/input.+id="contact\-name"/', $res);
		$this->assertRegExp('/input.+id="contact\-email"/', $res);
		$this->assertRegExp('/input.+id="contact\-phone"/', $res);

		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'contact-next',
			'license-name' => sprintf('Test License %06x', $this->_pid),
			'license-id' => '',
			'company-id' => '',
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'email' => USER_A_USERNAME,
			'phone' => '1234567890',
		]]);
		$this->assertValidResponse($res, 302);

		$l = $res->getHeaderLine('location');
		$this->assertEquals('/done?e=cac065', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);

	}

	function test_account_create_fail_email()
	{
		$c = $this->_ua();

		// Create0/GET
		$res = $c->get('/account/create');
		$res = $this->assertValidResponse($res);

		$this->assertRegExp('/Create Account/', $res);
		$this->assertRegExp('/input.+id="license\-name"/', $res);
		$this->assertRegExp('/input.+id="contact\-name"/', $res);
		$this->assertRegExp('/input.+id="contact\-email"/', $res);
		$this->assertRegExp('/input.+id="contact\-phone"/', $res);

		// Create1/POST
		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'contact-next',
			'license-name' => sprintf('Test License %06x', $this->_pid),
			'license-id' => '',
			'company-id' => '',
			'contact-name' => sprintf('Test Contact %06x', $this->_pid),
			'email' => 'invalid.email-typeA',
			'phone' => '1234567890',
		]]);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/account/create?e=cac035', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);


	}

	function test_account_password_reset()
	{
		$c = $this->_ua();

		// GET
		$res = $c->get('/auth/open');
		$res = $this->assertValidResponse($res);
		$this->assertStringContainsString('/auth/once?a=password-reset', $res);

		// GET
		$res = $c->get('/auth/once?a=password-reset');
		$this->assertValidResponse($res);
		$this->assertStringContainsString('<input autofocus class="form-control" inputmode="email" name="username" placeholder="email" type="email" value="">', $this->raw);
		$this->assertStringContainsString('<button class="btn btn-success" name="a" type="submit" value="password-reset-request">Request Password Reset</button>', $this->raw);

		// POST
		$res = $c->post('/auth/once?a=password-reset', [ 'form_params' => [
			'a' => 'password-reset-request',
			'username' => USER_A_USERNAME,
		]]);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/done?e=cao100&l=200&s=t', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);
		echo "<<<<\n{$this->raw}\n####\n";
		$this->assertStringContainsString('', $this->raw);
		$this->assertGreaterThan(1024, strlen($this->raw));

	}

	// function test_account_update()
	// {

	// }

	// function test_account_lockout()
	// {
	// 	// Fail Password Three Times
	// 	$c = $this->_ua();
	// 	$res = $c->get('/auth/open');

	// }

}
