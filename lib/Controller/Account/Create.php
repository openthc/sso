<?php
/**
 * Create Account
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Account;

use OpenTHC\SSO\CSRF;
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

		$cfg = \OpenTHC\Config::get('google');
		$data['Google']['recaptcha_public'] = $cfg['recaptcha-public'];

		return $RES->write( $this->render('account/create.php', $data) );
	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		// _check_recaptcha();
		$chk = CSRF::verify($_POST['CSRF']);
		if (empty($chk)) {
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
				return $RES->withRedirect('/done?' . http_build_query($ret_args));
				break;
			case 400:
				// Not Allowed?
				return $RES->withRedirect('/account/create?e=CAC-035');
			case 404:
				// Excellent
				break;
			case 410:
				// Not Allowed?
				$ret_args['e'] = 'CAC-091';
				return $RES->withRedirect('/done?' . http_build_query($ret_args));
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
			'account' => $_POST,
			'ip' => $_SERVER['REMOTE_ADDR'],
		];
		// Auth Hash Link - Redis
		// $tok = Auth_Context_Ticket::set($act_data);
		// Auth Hash Link - PostgreSQL
		$dbc_auth = $this->_container->DBC_AUTH;
		$act = new \OpenTHC\Auth_Context_Ticket($dbc_auth);
		$act->create($act_data);
		$RES = $RES->withAttribute('Auth_Context_Ticket', $act['id']);

		$ret_args['e'] = 'CAC-111';

		// Test Mode
		if ('TEST' == getenv('OPENTHC_TEST')) {
			$ret_args['t'] = $act['id'];
		}

		return $RES->withRedirect('/done?' . http_build_query($ret_args));

	}
}
