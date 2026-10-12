<?php
/**
 * Create Account
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Account;

use OpenTHC\CSRF;
use OpenTHC\SSO\Auth_Context_Ticket;

class Create extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page'] = [ 'title' => 'Create Account' ];

		if ( ! empty($_GET['r'])) {
			$_SESSION['return-path'] = $_GET['r'];
		}

		switch ($_GET['e']) {
		case 'CAC-035':
			// Invalid Email Address
			$data['Page']['flash'] = '<div class="alert alert-warning">Invalid Email Address</div>';
			break;
		case 'CAC-049':
			// Invalid Email Address
			$data['Page']['flash'] = '<div class="alert alert-warning">Invalid Request</div>';
			break;
		}

		$x = \OpenTHC\Config::get('google/recaptcha-public');
		$data['Google']['recaptcha_public'] = $x;

		$RES->getBody()->write( $this->render('account/create.php', $data) );

		return $RES;
	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$chk = CSRF::verify($_POST['CSRF']);
		if (empty($chk)) {
			return $this->redirect('/account/create?e=CAC-049');
		}

		$chk = $this->_verify_captcha();
		if (true !== $chk) {
			return $RES->withRedirect('/account/create?e=CAC-049');
		}

		switch ($_POST['a']) {
		case 'contact-next':
			return $this->_create($RES);
		}

		$RES->withJSON([
			'data' => null,
			'meta' => [ 'note' => 'Invalid Request [CAC-055]' ],
		], 400);

	}

	/**
	 * Create Account Process
	 */
	private function _create($RES)
	{
		$ret_args = [];

		$sso = new \OpenTHC\Service\OpenTHC('sso');
		$url = sprintf('/api/contact?q=%s', rawurlencode($_POST['contact-email']));
		$res = $sso->get($url);
		switch ($res['code']) {
			case 102:
			case 200:
				$Contact = $res['data'];
				$RES = $RES->withAttribute('Contact', $Contact);
				switch ($Contact['stat']) {
					case 100:
					case 102:
						$ret_args['e'] = 'CAC-083';
						break;
					case 200:
						$ret_args['e'] = 'CAC-086';
						break;
					default:
						$ret_args['e'] = 'CAC-089';
						break;

				}
				return $this->redirect('/done?' . http_build_query($ret_args));
				break;
			case 400:
				// Not Allowed?
				return $this->redirect('/account/create?e=CAC-035');
			case 404:
				// Excellent
				break;
			case 410:
				// Not Allowed?
				$ret_args['e'] = 'CAC-091';
				return $this->redirect('/done?' . http_build_query($ret_args));
				break;
			default:
				throw new \Exception('Invalid Response [CAC-089]');
		}

		// Store Data on Response (for Middleware)
		$RES = $RES->withAttribute('mode', 'account-create');
		$RES = $RES->withAttribute('Contact', [
			'email' => $res['data']['email'],
		]);

		// Make Ticket and Redirect
		$act_data = [
			'intent' => 'account-create',
			'service' => $_GET['service'],
			// @note There is disagreement on whether or not to include a cleaned-up contact object here with context from the 'account' object
			'account' => $_POST,
			'ip' => $_SERVER['REMOTE_ADDR'],
		];

		// Auth Hash Link - Redis
		$tok = Auth_Context_Ticket::set($act_data, 900);
		$RES = $RES->withAttribute('Auth_Context_Ticket', $tok);

		// Auth Hash Link - PostgreSQL
		// $dbc_auth = $this->dic->get('DBC_AUTH');
		// $act = new \OpenTHC\Auth_Context_Ticket($dbc_auth);
		// $act->create($act_data);
		// $RES = $RES->withAttribute('Auth_Context_Ticket', $act['id']);

		$ret_args['e'] = 'CAC-111';

		// Test Mode
		if (is_test_mode()) {
			$ret_args['t'] = $tok;
		}

		$x = $RES->getAttribute('Contact');
		unset($x['password']);
		$this->_container->RDB->publish('openthc/sso/account/create', json_encode([
			'Contact' => $x,
			'token' => $tok,
		]));

		return $this->redirect('/done?' . http_build_query($ret_args));

	}

	private function _verify_captcha()
	{
		$sk = \OpenTHC\Config::get('google/recaptcha-secret');
		if (empty($sk)) {
			return true;
		}

		if (empty($_POST['g-recaptcha-response'])) {
			return false; // RES->withRedirect('/auth/fail?e=cao290');
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$req = _curl_init($url);

		$arg = [
			'secret' => $sk,
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_SERVER['REMOTE_ADDR'],
		];
		curl_setopt_array($req, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($arg)
		]);

		$res = curl_exec($req);
		$inf = curl_getinfo($req);
		if (200 != $inf['http_code']) {
			return false;
			return $RES->withRedirect('/auth/fail?e=cao316');
		}

		$res = json_decode($res, true);
		if (empty($res)) {
			return false;
		}
		if (empty($res['success'])) {
			return false;
		}

		return true;

	}
}
