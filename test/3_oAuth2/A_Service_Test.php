<?php
/**
 * Quick Test of oAuth via CIC
 */

namespace Test\oAuth2;

class A_oAuth_Test extends \Test\Base_Case
{
	function test_auth_pass()
	{
		$sso_ua = $this->_ua();

		// $cic = new \OpenTHC\Service('cic');
		$cic_ua = new \GuzzleHttp\Client(array(
			'base_uri' => 'https://cic.openthc.dev/',
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));


		// Get a Service Page
		$res = $cic_ua->get('/auth/open');
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertRegExp('/https:\/\/sso.openthc.+authorize.+scope.+state.+client_id/', $l);

		// Get the oAuth Authorize Page
		$res = $sso_ua->get($l);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertRegExp('/https:\/\/sso.openthc.+auth\/open.+a=oauth&r=.+/', $l);

		// Get the Open Page
		$res = $sso_ua->get($l);
		$this->assertValidResponse($res);

		// Post the Open Page
		$res = $sso_ua->post($l, [ 'form_params' => [
			'a' => 'sign in',
			'username' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			'password' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
		]]);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/auth/init', $l);

		// GET /auth/init
		$res = $sso_ua->get($l);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		// var_dump($l);

		// GET /oauth2/authorize
		$res = $sso_ua->get($l);
		$this->assertValidResponse($res);

		// print_r($this->raw);

		$permit_link = preg_match('/href="(\/oauth2\/permit\?_=[\w\-]+)"/', $this->raw, $m) ? $m[1] : null;
		$this->assertNotEmpty($permit_link);
		// var_dump($permit_link);

		$reject_link = preg_match('/href="(\/oauth2\/reject\?_=[\w\-]+)"/', $this->raw, $m) ? $m[1] : null;
		$this->assertNotEmpty($reject_link);


		$res = $sso_ua->get($permit_link);
		$this->assertValidResponse($res);
		// print_r($this->raw);

		$link_continue = preg_match('/href="(https.+)">Continue/', $this->raw, $m) ? $m[1] : null;
		$link_continue = html_entity_decode($link_continue, ENT_COMPAT | ENT_HTML5, 'utf-8');
		// var_dump($link_continue);
		$this->assertNotEmpty($link_continue);
		$this->assertRegExp('/https:.+\/auth\/back\?code=.+state=.+/', $link_continue);

		$res = $cic_ua->get($link_continue);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertEquals('/home', $l);
		$res = $cic_ua->get($l);
		$this->assertValidResponse($res);

	}

	function test_auth_fail()
	{

	}

}
