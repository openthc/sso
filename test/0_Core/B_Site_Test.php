<?php
/**
 * Base Class for API Testing
 */

namespace Test\Core;

class B_Site_Test extends \Test\Base_Case
{
	function test_auth_pass()
	{
		$cfg = $_ENV['test-host'];
		$this->assertIsString($cfg);
		$this->assertMatchesRegularExpression('/\w+\.\w{2,256}\.\w{2,16}$/', $cfg);

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

		$res = $ghc->get('/done');
		$this->assertValidResponse($res);

	}

}
