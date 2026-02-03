<?php
/**
 * Set a Password
 * @todo move to auth/once/password or something?
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Account;

use OpenTHC\CSRF;
use OpenTHC\SSO\Auth_Context_Ticket;

class Password extends \OpenTHC\SSO\Controller\Base
{
	/**
	 * HTTP GET handler
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	 * @return \Slim\Http\Response
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$ARG = $this->parseArg();

		$data = $this->data;
		$data['Page']['title'] = 'Account :: Password Update';
		$data['auth_username'] = $ARG['contact']['username'];
		$data['CSRF'] = CSRF::getToken();

		if (!empty($_GET['e'])) {
			switch ($_GET['e']) {
			case 'CAP-047':
				$data['Page']['flash'] = 'Invalid Password [CAP-047]';
				break;
			case 'CAP-052':
				$data['Page']['flash'] = 'Invalid Password [CAP-052]';
				break;
			case 'CAP-062':
				$data['Page']['flash'] = 'Passwords do not match [CAP-062]';
				break;
			}
		}

		$RES->getBody()->write( $this->render('account/password.php', $data) );

		return $RES;

	}

	/**
	 * HTTP POST handler
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	 * @return \Slim\Http\Response
	 */
	function post($REQ, $RES, $ARG)
	{
		CSRF::verify($_POST['CSRF']);

		$ARG = $this->parseArg();

		// Set Their Password
		switch (strtolower($_POST['a'])) {
		case 'update':

			$p = $_POST['p0'];

			if (empty($p) || empty($_POST['p1'])) {
				return $this->_redirect_internal($ARG, 'CAP-047');
			}

			if (strlen($p) < 8) {
				return $this->_redirect_internal($ARG, 'CAP-052');
			}

			if ($p != $_POST['p1']) {
				return $this->_redirect_internal($ARG, 'CAP-062');
			}

			$dbc_auth = $this->dic->get('DBC_AUTH');

			$arg = [];
			$arg[':c0'] = $ARG['contact']['id'];
			$arg[':pw'] = password_hash($_POST['p0'], PASSWORD_DEFAULT);

			$sql = 'UPDATE auth_contact SET password = :pw WHERE id = :c0';
			$dbc_auth->query($sql, $arg);

			// Log It
			$dbc_auth->insert('log_event', [
				'contact_id' => $ARG['contact']['id'],
				'code' => 'Contact/Password/Update',
				'meta' => json_encode($_SESSION),
			]);

			// For Middleware
			$RES = $RES->withAttribute('Contact', [
				'id' => $ARG['contact']['id'],
				'username' => $ARG['contact']['username'],
				'password' => $arg[':pw'],
			]);

			return $this->_redirect_internal($ARG, null);

			break;
		}
	}

	/**
	 * Parse Incoming Arguments
	 */
	private function parseArg()
	{
		if (empty($_GET['_'])) {
			_exit_html_fail('<h1>Invalid Request [CAP-129]</h1>', 400);
		}

		// Load Auth Ticket or DIE
		$ARG = \OpenTHC\SSO\Auth_Context_Ticket::get($_GET['_']);
		if (empty($ARG)) {
			_exit_html_warn('<h1>Invalid Request [CAP-133]</a></h1>', 400);
		}

		switch ($ARG['intent']) {
		case 'account-create':
		case 'account-invite':
		case 'password-reset':
		case 'password-update': // @deprecated?
			// OK
			break;
		default:
			_exit_html_fail('<h1>Invalid Request [CAP-110]</h1>', 400);
		}

		return $ARG;
	}

	/**
	 * Smart Redirector from Context
	 */
	function _redirect_internal($act, $err)
	{
		$arg = [
			'_' => $_GET['_'],
			'e' => $err,
		];

		$path = '/account/password';
		switch ($act['intent']) {
			case 'account-create':
			case 'account-invite':
				$path = '/verify/password';
				if (empty($err)) {
					// Success
					$path = '/verify';
				}
				break;
			case 'password-reset':
			case 'password-update':
				if (empty($err)) {
					// Success
					$path = '/auth/open';
					$arg = [
						'e' => 'CAP-080'
					];
				}
		}

		// Re-Generate Token?

		$arg = http_build_query($arg);

		$url = sprintf('%s?%s', $path, $arg);

		return $this->redirect($url);

	}
}
