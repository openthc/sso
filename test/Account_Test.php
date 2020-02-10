<?php
/**
 * Base Class for API Testing
 */

namespace Test;

class Account_Test extends \Test\Base_Test_Case
{
	function test_account_create()
	{
		$c = $this->_api();
		$res = $c->get('/account/create');
		$this->assertValidResponse($res);

		$html = $this->raw; //$res->getBody()->getContents();

		$this->assertRegExp('/select.+id="account-region"/', $html);
		$this->assertRegExp('/button.+id="btn-region-next"/', $html);

		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'region-next',
			'region' => 'xxx/xx',
		]]);

		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/account/create', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);

		$html = $this->raw;
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
		$this->assertEquals('/done?e=cac111', $l);

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
		$c = $this->_api();
		$res = $c->get('/account/create');
		$this->assertValidResponse($res);

		$html = $this->raw; //$res->getBody()->getContents();

		$this->assertRegExp('/select.+id="account-region"/', $html);
		$this->assertRegExp('/button.+id="btn-region-next"/', $html);

		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'region-next',
			'region' => 'xxx/xx',
		]]);

		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/account/create', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);

		$html = $this->raw;
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
		$c = $this->_api();
		$res = $c->get('/account/create');
		$this->assertValidResponse($res);

		$html = $this->raw; //$res->getBody()->getContents();

		$this->assertRegExp('/select.+id="account-region"/', $html);
		$this->assertRegExp('/button.+id="btn-region-next"/', $html);

		$res = $c->post('/account/create', [ 'form_params' => [
			'a' => 'region-next',
			'region' => 'xxx/xx',
		]]);

		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/account/create', $l);

		$res = $c->get($l);
		$this->assertValidResponse($res);

		// $l = $res->getHeaderLine('location');
		// $this->assertEquals('/account/create?e=cac035', $l);

	}

	// function test_account_password_reset()
	// {
	// 	$c = $this->_api();
	// 	$res = $c->get('/auth/open');
	// 	$this->assertValidResponse($res);

	// }

	// function test_account_update()
	// {

	// }

	// function test_account_lockout()
	// {
	// 	// Fail Password Three Times
	// 	$c = $this->_api();
	// 	$res = $c->get('/auth/open');

	// }

	private function _api()
	{
		$c = new \GuzzleHttp\Client(array(
			'base_uri' => TEST_SITE,
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		return $c;

	}
}
