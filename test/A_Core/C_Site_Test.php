<?php
/**
 * Base Class for API Testing
 */

namespace OpenTHC\SSO\Test\A_Core;

class C_Site_Test extends \OpenTHC\SSO\Test\Base_Case
{
	function test_page_all()
	{
		$cfg = getenv('OPENTHC_TEST_ORIGIN');
		$this->assertIsString($cfg);
		$this->assertMatchesRegularExpression('/\w+\.\w{2,256}\.\w{2,16}$/', $cfg);

		$ghc = new \GuzzleHttp\Client([
			'base_uri' => $cfg,
			'cookies' => true,
			'http_errors' => false,
		]);

		$res = $ghc->get('/');
		$this->assertValidResponse($res);

		$res = $ghc->get('/auth/open');
		$this->assertValidResponse($res);

		// $res = $ghc->get('/.well-known/change-password');
		// $this->assertValidResponse($res);

		$res = $ghc->get('/auth/open?a=password-reset');
		$this->assertValidResponse($res);

		$res = $ghc->get('/account/create');
		$this->assertValidResponse($res);

		$res = $ghc->get('/auth/shut');
		$this->assertValidResponse($res);

		$res = $ghc->get('/done');
		$this->assertValidResponse($res);

	}

	function test_site_test_mode()
	{
		$sso = $this->_ua();
		$res = $sso->get(sprintf('/auth/open?_t=%s', getenv('OPENTHC_TEST_HASH')));
		$res = $this->assertValidResponse($res);

		$this->assertMatchesRegularExpression('/TEST MODE/', $res);

	}

}
