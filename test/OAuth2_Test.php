<?php

namespace OpenTHC\SSO\Test;

class OAuth2_Test extends Base_Case
{
	/**
	 * Test OAuth 2.0 Client Authorization Workflow (Happy Case)
	 */
	public function testAuthorizationFlow()
	{
		// Client Information (replace with your test client details)
		$clientId = OPENTHC_TEST_SERVICE_A;
		$redirectUri = sprintf("%s/auth/back", OPENTHC_TEST_SERVICE_A_ORIGIN) . '?r=%2Fmarket';
		$scopes = 'b2b company contact';

		// User Login ( Simulate successful login with dummy data )
		$user = [
			'id' => OPENTHC_TEST_CONTACT_A, // Replace with a valid user ID
			'company_id' => '01HZ89M9S6E29STTBKFFCBCV6H', // Replace with a valid company ID
		];
		$_SESSION['Contact'] = $user;
		$_SESSION['Company'] = ['id' => $user['company_id']];

		// Initial Request (GET)
		$res = $this->_ua()->get('/oauth2/authorize', [
			'query' => [
				'client_id' => $clientId,
				'redirect_uri' => $redirectUri,
				'response_type' => 'code',
				'scope' => $scopes,
				'state' => 'some_unique_state_value',
			],
		]);

		// Assertions - Initial Request
		$this->assertValidResponse($res, 302);
		$l0 = $res->getHeaders();
		$l0 = $l0['Location'][0];
		$x = parse_url($l0);
		$x = parse_str($x['query'], $state);
		
		$res = $this->_ua()->get($l0);
		$this->assertValidResponse($res, 200);

		// $this->assertContains('title="Authorize"', $this->raw); // Check for authorization prompt title

		// Simulate User Approval (Mock user clicking "Authorize")
		//  (In reality, user would interact with the authorization prompt)

		// Confirmation Request (POST) - Simulate form submission with CSRF token
		$csrf = $this->getCSRF($this->raw);
		$res = $this->_ua()->get('/oauth2/permit', [
			'query' => [
				'CSRF' => $csrf,
				'auth-commit' => 'true', // Simulate remembering authorization
				// Other form fields from the authorization prompt (replace with actual values)
				'client_id' => $clientId,
				'redirect_uri' => $redirectUri,
				'scope' => $scopes,
				'state' => $state['_'],
				'_' => $state['_'],
			],
		]);

		// Assertions - Confirmation Request (Permit)
		// var_dump($res);
		$this->assertValidResponse($res, 302); // Redirect expected

		// Follow the Redirect (manually for testing purposes)
		$location = $res->getHeaderLine('Location');
		$redirect_parts = parse_url($location);

		// Assertions - Redirect URI
		$this->assertEquals('https', $redirect_parts['scheme']);
		$this->assertEquals($redirectUri, $redirect_parts['path']);
		$this->assertArrayHasKey('code', $redirect_parts['query']); // Authorization code expected in query string
		$this->assertArrayHasKey('state', $redirect_parts['query']); // State value should be preserved
	}
}
