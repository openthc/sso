<?php
/**
 * Test the Pages
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\A_Core;

class C_Site_Test extends \OpenTHC\SSO\Test\Base
{
	protected $type_expect = 'text/html';

	function test_page_all()
	{
		$cfg = $_ENV['OPENTHC_TEST_ORIGIN'];
		$this->assertIsString($cfg);
		$this->assertMatchesRegularExpression('/\w+\.\w{2,256}\.\w{2,16}$/', $cfg);

		$ghc = $this->_ua();

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
