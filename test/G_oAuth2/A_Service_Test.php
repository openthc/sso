<?php
/**
 * Quick Test of oAuth via OPS
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\G_oAuth2;

class A_Service_Test extends \OpenTHC\SSO\Test\Base
{
	protected $type_expect = 'text/html';

	/**
	 * Test service connection to App
	 */
	function test_auth_pass_app()
	{
		$sso_ua = $this->_ua();

		$url = \OpenTHC\Config::get('openthc/app/origin');
		$this->assertNotEmpty($url);

		$app_ua = new \GuzzleHttp\Client(array(
			'base_uri' => $url,
			'allow_redirects' => false,
			'debug' => $_ENV['OPENTHC_TEST_HTTP_DEBUG'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		// Get a Service Page
		$res = $app_ua->get('/auth/open');
		$this->assertValidResponse($res, 302);
		// $url = $res->getHeaderLine('location');
		// var_dump($url);
		// $this->assertNotEmpty($url);
		// $this->assertMatchesRegularExpression('/https:\/\/sso.openthc.+authorize.+scope.+state.+client_id/', $l);
	}

	/**
	 * Test service connection to B2B Marketplace
	 */
	function test_auth_pass_b2b()
	{
		$sso_ua = $this->_ua();

		$url = \OpenTHC\Config::get('openthc/b2b/origin');
		$this->assertNotEmpty($url);

		$b2b_ua = new \GuzzleHttp\Client(array(
			'base_uri' => $url,
			'allow_redirects' => false,
			'debug' => $_ENV['OPENTHC_TEST_HTTP_DEBUG'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		// Get a Service Page
		$res = $b2b_ua->get('/auth/open');
		$this->assertValidResponse($res, 302);
	}

	/**
	 *
	 */
	function test_auth_pass_ops()
	{
		$sso_ua = $this->_ua();

		$url = \OpenTHC\Config::get('openthc/ops/origin');
		$ops_ua = new \GuzzleHttp\Client(array(
			'base_uri' => $url,
			'allow_redirects' => false,
			'debug' => $_ENV['OPENTHC_TEST_HTTP_DEBUG'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));


		// Get a Service Page
		$res = $ops_ua->get('/auth/open');
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/https:\/\/sso.openthc.+authorize.+scope.+state.+client_id/', $l);


		// Get the oAuth Authorize Page
		$res = $sso_ua->get($l);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/auth\/open\?_=[\w\-]+/', $l);


		// Get the Open Page
		$res = $sso_ua->get($l);
		$html = $this->assertValidResponse($res);

		// Post the Open Page
		$arg = [
			'CSRF' => $this->getCSRF($html),
			'a' => 'account-open',
			'username' => $_ENV['OPENTHC_TEST_CONTACT_0'],
			'password' => $_ENV['OPENTHC_TEST_CONTACT_PASSWORD'],
		];
		$res = $sso_ua->post($l, [ 'form_params' => $arg ]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/^\/auth\/init\?_=.+/', $url);

		// GET /auth/init
		$res = $sso_ua->get($url);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');

		// GET /notify
		$this->assertMatchesRegularExpression('/^\/notify\?r=.+/', $url);
		$res = $sso_ua->get($url);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');

		// GET /oauth2/authorize
		$res = $sso_ua->get($url);
		$this->assertValidResponse($res);
		// file_put_contents(APP_ROOT . '/test_auth_pass.html', $this->raw);

		// Should be the Verify Page?
		// NO!  That should have been done in a previous test
		$this->assertStringContainsString('Application Authorization', $this->raw);

		$permit_link = preg_match('/href="(\/oauth2\/permit\?_=[\w\-]+)"/', $this->raw, $m) ? $m[1] : null;
		$this->assertNotEmpty($permit_link);
		// // var_dump($permit_link);

		$reject_link = preg_match('/href="(\/oauth2\/reject\?_=[\w\-]+)"/', $this->raw, $m) ? $m[1] : null;
		$this->assertNotEmpty($reject_link);

		$res = $sso_ua->get($permit_link);
		$this->assertValidResponse($res);
		// print_r($this->raw);

		$link_continue = preg_match('/href="(https[^"]+)">Continue/', $this->raw, $m) ? $m[1] : null;
		$link_continue = html_entity_decode($link_continue, ENT_COMPAT | ENT_HTML5, 'utf-8');
		// var_dump($link_continue);
		$this->assertNotEmpty($link_continue);
		$this->assertMatchesRegularExpression('/https:.+\/auth\/back\?code=.+state=.+/', $link_continue);

		// OPS /auth/back
		$res = $ops_ua->get($link_continue);
		$this->assertValidResponse($res, 302);
		$url1 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/\/auth\/init/', $url1);
		// $this->assertEquals('/dashboard', $url1);

		$res = $ops_ua->get($url1);
		$this->assertValidResponse($res, 302);
		$url2 = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/\/dashboard/', $url2);

		$res = $ops_ua->get($url2);

		// Should Get Authenticated but then Rejected by OPS because of permissions
		// Get Dashboard, Denied by ACL
		// $res = $ops_ua->get($url1);
		$html = $this->assertValidResponse($res);
		// $this->assertStringContainsString('Access Denied [ACL-092]', $html);

		// $res = $ops_ua->get($url1);
		// $this->assertValidResponse($res, 302);
		// $url2 = $res->getHeaderLine('location');
		// var_dump($url2);

	}

}
