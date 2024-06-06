<?php

namespace OpenTHC\SSO\Test;

class OAuth2_Test extends Base_Case
{
	/**
	 * Test OAuth 2.0 Client Authorization Workflow (Happy Case)
	 */
	public function testAuthorizationFlow()
	{
		$ghc = $this->_ua();
		// Client Information (replace with your test client details)
		$clientId = OPENTHC_TEST_SERVICE_A;
		$redirectUri = sprintf("%s/auth/back", OPENTHC_TEST_SERVICE_A_ORIGIN);
		$scopes = 'b2b company contact';
		$state = null; // @todo Stub in the session state

		// User Login ( Simulate successful login with dummy data )
		$user = [
			'id' => OPENTHC_TEST_CONTACT_A, // Replace with a valid user ID
			'company_id' => '01HZ89M9S6E29STTBKFFCBCV6H', // Replace with a valid company ID
		];
		$_SESSION['Contact'] = $user;
		$_SESSION['Company'] = ['id' => $user['company_id']];

		// Initialize `oauth2-state` in external service _SESSION ; this is a representation of $state
		// @todo is session and state really synonymous the OAuth2 workflow?
		$res = $ghc->get(sprintf('%s/auth/open', OPENTHC_TEST_SERVICE_A_ORIGIN) . '?r=%2Fmarket' );
		$this->assertValidResponse($res, 302);
		$l = $res->getHeaders()['Location'][0];
		$this->assertMatchesRegularExpression('/\/oauth2\/authorize/', $l);
		$x = parse_url($l);
		$x = parse_str($x['query'], $query);
		$state = $query['state'];
		$this->assertNotEmpty($state);

		// Initial Request (GET)
		$res = $ghc->get('/oauth2/authorize', [
			'query' => [
				'client_id' => $clientId,
				'redirect_uri' => $redirectUri,
				'response_type' => 'code',
				'scope' => $scopes,
				'state' => $state,
			],
		]);

		// Assertions - Initial Request
		$this->assertValidResponse($res, 302);

		// Redirection to /auth/open
		$l0 = $res->getHeaders();
		$l0 = $l0['Location'][0];
		$x0 = parse_url($l0);
		$x0 = parse_str($x0['query'], $state0);
		$this->assertMatchesRegularExpression('/\/auth\/open\?_=.+/', $l0);

		// Assertions against /auth/open
		// $l0 is the URL with our context token
		$res = $ghc->get($l0);
		$this->assertValidResponse($res, 200);

		// Sign-in with test credentials
		$csrf = $this->getCSRF($this->raw);
		$res = $ghc->post($l0, [
			'form_params' => [
				'CSRF' => $csrf,
				'username' => OPENTHC_TEST_CONTACT_A,
				'password' => OPENTHC_TEST_CONTACT_PASSWORD,
				'a' => 'account-open',
			],
		]);
		$this->assertValidResponse($res, 302);

		// Redirection to /auth/init
		$l1 = $res->getHeaders();
		$l1 = $l1['Location'][0];
		$x1 = parse_url($l1);
		$x1 = parse_str($x1['query'], $state1);
		$this->assertMatchesRegularExpression('/\/auth\/init\?_=.+/', $l1);

		// Assertions against /auth/init
		// $l1 is the URL with our new(?) context token
		$res = $ghc->get($l1);
		$this->assertValidResponse($res, 302);

		// Redirection to /oauth2/authorize
		$l2 = $res->getHeaders();
		$l2 = $l2['Location'][0];
		$x2 = parse_url($l2);
		$x2 = parse_str($x2['query'], $state2);
		$this->assertMatchesRegularExpression('/\/oauth2\/authorize\?.+/', $l2);
		$this->assertEquals($state2['state'], $state);
		$res = $ghc->get('/oauth2/authorize', [
			'query' => [
				'client_id' => $clientId,
				'redirect_uri' => $redirectUri,
				'response_type' => 'code',
				'scope' => $scopes,
				'state' => $state,
			]
		]);

		// OAuth Permit
		$this->assertValidResponse($res, 200);
		$this->assertMatchesRegularExpression('/<title>Authorize<\/title>/', $this->raw); // Check for authorization prompt title
		preg_match('/href=\"(\/oauth2\/permit\?_=.+)\"/', $this->raw, $match);
		$l3 = $match[1];
		$x3 = parse_url($l3);
		parse_str($x3['query'], $state3); // Introduces the magic _ parameter into the workflow
		$this->assertMatchesRegularExpression('/\/oauth2\/permit\?_=.+/', $l3);
		$res = $ghc->get('/oauth2/permit', [
			'query' => [
				'_' => $state3['_'],
			],
		]);
		$this->assertValidResponse($res, 200);

		// OAuth Permit Continues to Service
		preg_match('/id=\"oauth2-permit-continue\" href=\"(.*)\">Continue/', $this->raw, $match);
		$l4 = $match[1];
		$x4 = parse_url($l4);
		parse_str($x4['query'], $state4);
		$this->assertMatchesRegularExpression('/\/auth\/back\?.+/', $l4);
		// Test all $stateX for similarity or difference
		$this->assertEquals($state4['state'], $state);
		$this->assertNotEmpty($state4['code']);
		$res = $ghc->get($redirectUri, [
			'query' => [
				'code' => $state4['code'],
				'state' => $state,
			],
		]);
		$this->assertValidResponse($res, 302);

	}
}
