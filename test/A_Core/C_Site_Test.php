<?php
/**
 * Base Class for API Testing
 */

namespace OpenTHC\SSO\Test\A_Core;

class C_Site_Test extends \OpenTHC\SSO\Test\Base_Case
{
	function test_page_all()
	{
		$cfg = OPENTHC_TEST_ORIGIN;
		$this->assertIsString($cfg);
		$this->assertMatchesRegularExpression('/\w+\.\w{2,256}\.\w{2,16}$/', $cfg);

		$ghc = new \GuzzleHttp\Client([
			'base_uri' => $cfg,
			'allow_redirects' => false,
			'cookies' => true,
			'http_errors' => false,
		]);

		$res = $ghc->get('/');
		$this->assertValidResponse($res);

		$res = $ghc->get('/auth/open');
		$res = $this->assertValidResponse($res);
		$this->assertMatchesRegularExpression('/TEST MODE/', $res);

		$res = $ghc->get('/.well-known/change-password');
		$this->assertValidResponse($res, 302);

		$res = $ghc->get('/auth/open?a=password-reset');
		$this->assertValidResponse($res);

		$res = $ghc->get('/account/create');
		$this->assertValidResponse($res);

		$res = $ghc->get('/auth/shut');
		$this->assertValidResponse($res);

		$res = $ghc->get('/done');
		$this->assertValidResponse($res);

	}

}
