<?php
/**
 * Quick Test of oAuth via CIC
 */

namespace Test\G_oAuth2;

class A_oAuth_Test extends \Test\Base_Case
{
	function test_auth_pass_app()
	{
		$sso_ua = $this->_ua();

		$cfg = \OpenTHC\Config::get('openthc_app/hostname');
		$this->assertNotEmpty($cfg);

		$app_ua = new \GuzzleHttp\Client(array(
			'base_uri' => sprintf('https://%s/', $cfg),
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		// Get a Service Page
		$res = $app_ua->get('/auth/open?a=oauth');
		$this->assertValidResponse($res, 200);
		// $url = $res->getHeaderLine('location');
		// var_dump($url);
		// $this->assertNotEmpty($url);
		// $this->assertMatchesRegularExpression('/https:\/\/sso.openthc.+authorize.+scope.+state.+client_id/', $l);

	}

	function test_auth_pass_cic()
	{
		$sso_ua = $this->_ua();

		$cfg = \OpenTHC\Config::get('openthc_cic/hostname');
		$cic_ua = new \GuzzleHttp\Client(array(
			'base_uri' => sprintf('https://%s/', $cfg),
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
		$this->assertMatchesRegularExpression('/https:\/\/sso.openthc.+authorize.+scope.+state.+client_id/', $l);


		// Get the oAuth Authorize Page
		$res = $sso_ua->get($l);
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaderLine('location');
		$this->assertMatchesRegularExpression('/https:\/\/sso.openthc.+auth\/open\?_=[\w\-]+/', $l);


		// Get the Open Page
		$res = $sso_ua->get($l);
		$this->assertValidResponse($res);

		// Post the Open Page
		$res = $sso_ua->post($l, [ 'form_params' => [
			'a' => 'account-open',
			'username' => getenv('OPENTHC_TEST_CONTACT_USERNAME'),
			'password' => getenv('OPENTHC_TEST_CONTACT_PASSWORD'),
		]]);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		var_dump($url);
		$this->assertMatchesRegularExpression('/^\/auth\/init\?_=.+/', $url);

		// GET /auth/init
		$res = $sso_ua->get($url);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		var_dump($url);

		// GET /oauth2/authorize
		$res = $sso_ua->get($url);
		$this->assertValidResponse($res);
		file_put_contents(APP_ROOT . '/test_auth_pass.html', $this->raw);

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

		$link_continue = preg_match('/href="(https.+)">Continue/', $this->raw, $m) ? $m[1] : null;
		$link_continue = html_entity_decode($link_continue, ENT_COMPAT | ENT_HTML5, 'utf-8');
		// var_dump($link_continue);
		$this->assertNotEmpty($link_continue);
		$this->assertMatchesRegularExpression('/https:.+\/auth\/back\?code=.+state=.+/', $link_continue);

		$res = $cic_ua->get($link_continue);
		$this->assertValidResponse($res, 302);
		$url = $res->getHeaderLine('location');
		var_dump($url);
		$this->assertEquals('/dashboard', $url);

		// Should Get Authenticated but then Rejected by CIC because of permissions
		$res = $cic_ua->get($url);
		$html = $this->assertValidResponse($res, 403);
		$this->assertStringContainsString('Access Denied [CLA-087]', $html);

	}

}
