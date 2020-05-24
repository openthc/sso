<?php
/**
 * Base Class for API Testing
 */

namespace Test\Core;

class Site_Test extends \Test\Base_Test_Case
{
	function test_auth_pass()
	{
		$cfg = \OpenTHC\Config::get('application.hostname');
		$this->assertIsString($cfg);
		$this->assertRegExp('/\w+\.\w{2,256}\.\w{2,16}$/', $cfg);

		$ghc = new \GuzzleHttp\Client([
			'base_uri' => sprintf('https://%s', $cfg),
			'cookies' => true,
			'http_errors' => false,
		]);

		$res = $ghc->get('/');
		$this->assertValidResponse($res);


		$res = $ghc->get('/auth/open');
		$this->assertValidResponse($res);

		$res = $ghc->get('/auth/once?a=password-reset');
		$this->assertValidResponse($res);

		$res = $ghc->get('/account/create');
		$this->assertValidResponse($res);

		$res = $ghc->get('/auth/shut');
		$this->assertValidResponse($res);

	}

}
