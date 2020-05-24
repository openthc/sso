<?php
/**
 * Test the Authentication Pages
 */

namespace Test\Auth;

class Fire_Test extends \Test\Base_Test_Case
{
	function test_auth_pass()
	{
		$c = $this->_ua();
		$res = $c->get('/auth/open');
		$res = $this->assertValidResponse($res);

		$this->assertRegExp('/input.+id="username"/', $res);
		$this->assertRegExp('/input.+id="password" name="password" type="password"/', $res);

		$res = $c->post('/auth/open', [ 'form_params' => [
			'a' => 'sign in',
			'username' => USER_A_USERNAME,
			'password' => USER_A_PASSWORD,
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

		$this->assertRegExp('/input.+id="username"/', $res);
		$this->assertRegExp('/input.+id="password" name="password" type="password"/', $res);

		$res = $c->post('/auth/open', [ 'form_params' => [
			'a' => 'sign in',
			'username' => USER_A_USERNAME,
			'password' => USER_A_PASSWORD_FAIL,
		]]);
		$this->assertValidResponse($res, 302);

		$l = $res->getHeaderLine('location');
		$this->assertEquals('/auth/open?e=cao093', $l);

	}

}
