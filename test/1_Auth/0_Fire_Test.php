<?php
/**
 * Test the Authentication Pages
 */

namespace Test\Auth;

class Fire_Test extends \Test\Base_Case
{
	function test_auth_pass()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/open');
		$res = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/input.+id="username"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="password" name="password" type="password"/', $res);

		$res = $c->post('/auth/open', [ 'form_params' => [
			'a' => 'sign in',
			'username' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			'password' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
		]]);
		$this->assertValidResponse($res, 302);

		$l = $res->getHeaderLine('location');
		$this->assertEquals('/auth/init', $l);

	}

	function test_auth_fail()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/open');
		$res = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/input.+id="username"/', $res);
		$this->assertMatchesRegularExpression('/input.+id="password" name="password" type="password"/', $res);

		$res = $c->post('/auth/open', [ 'form_params' => [
			'a' => 'sign in',
			'username' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			'password' => sprintf('invalid-password-%08x', rand(10000, 99999)),
		]]);
		$this->assertValidResponse($res, 302);

		$l = $res->getHeaderLine('location');
		$this->assertEquals('/auth/open?e=cao093', $l);

	}

}
