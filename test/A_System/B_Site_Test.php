<?php
/**
 * Base Class for API Testing
 */

namespace Test\A_System;

class B_Site_Test extends \Test\Base_Case
{
	function test_page_all()
	{
		$cfg = getenv('OPENTHC_TEST_HOST');
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
