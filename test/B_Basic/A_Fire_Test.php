<?php
/**
 * Test the Authentication Pages
 */

namespace OpenTHC\SSO\Test\B_Basic;

class A_Fire_Test extends \OpenTHC\SSO\Test\Base_Case
{
	/**
	 *
	 */
	function test_auth_open()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/open');
		$html = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/input.+name="CSRF"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="username"/', $html);
		$this->assertMatchesRegularExpression('/input.+id="password" name="password" type="password"/', $html);

		$res = $c->post('/auth/open', [ 'form_params' => [
			'CSRF' => $this->getCSRF($html),
			'a' => 'account-open',
			'username' => 'invalid-email@invalid-domain',
			'password' => 'invalid-password',
		]]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		$this->assertEquals('/auth/open?e=CAO-049', $url);

		$res = $c->get($url);
		$html = $this->assertValidResponse($res);

		// file_put_contents('Fire_Test_test_auth_open.html', $html);
		$this->assertMatchesRegularExpression('/Invalid email, please use a proper email address/', $html);
	}

	/**
	 *
	 */
	function test_auth_open_reset()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/open?a=password-reset');
		$html = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/input.+id="username"/', $html);

		$res = $c->post('/auth/open?a=password-reset', [ 'form_params' => [
			'CSRF' => $this->getCSRF($html),
			'a' => 'password-reset-request',
			'username' => 'invalid-email@invalid-domain',
		]]);
		$this->assertValidResponse($res, 302);

		$url = $res->getHeaderLine('location');
		$this->assertEquals('/auth/open?a=password-reset&e=CAO-049', $url);

		// Fetch Link
		$res = $c->get($url);
		$this->assertValidResponse($res);
		// file_put_contents('Fire_Test_test_auth_open_reset.html', $this->raw);
		$this->assertMatchesRegularExpression('/Invalid email, please use a proper email address/', $this->raw);

	}

	/**
	 *
	 */
	function test_auth_once()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/once');
		$res = $this->assertValidResponse($res, 400, 'text/plain');
	}

	/**
	 *
	 */
	function test_auth_ping()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/ping');
		$res = $this->assertValidResponse($res, 200, 'application/json');
	}

	/**
	 *
	 */
	function test_auth_init()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/init');
		$res = $this->assertValidResponse($res, 400, 'text/html');
	}

	/**
	 * Make Sure Fail is Nice
	 */
	function test_shut()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/shut');
		$res = $this->assertValidResponse($res);

	}

}
