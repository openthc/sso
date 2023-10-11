<?php
/**
 * One Time Link Handler
 *
 * SPDX-License-Identifier: MIT
 *
 * @todo merge into the "Open" controller
 */

namespace OpenTHC\SSO\Controller\Auth;

use OpenTHC\SSO\Auth_Contact;

class Once extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		session_regenerate_id(true);

		$_SESSION = [];

		// Token Links
		if (empty($_GET['_'])) {
			return $this->sendFailure($RES, [
				'error_code' => 'CAO-016',
				'fail' => 'Invalid Request',
			]);
		}

		if (!preg_match('/^([\w\-]{32,128})$/i', $_GET['_'], $m)) {
			return $this->sendFailure($RES, [
				'error_code' => 'CAO-022',
				'fail' => 'Invalid Request',
			]);
		}

		// Get Token
		$act = \OpenTHC\SSO\Auth_Context_Ticket::get($_GET['_']);
		if (empty($act)) {
			$act = new \OpenTHC\Auth_Context_Ticket($this->_container->DBC_AUTH, $_GET['_']);
			if ( ! $act->isValid()) {
				return $this->sendFailure($RES, [
					'error_code' => 'CAO-040',
					'fail' => 'Invalid Request',
				]);
			}
			$act = $act->getMeta();
		}

		// Intention Router
		switch ($act['intent']) {
			case 'account-create':
			case 'account-invite':
				// Make a New Token
				$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act);
				return $RES->withRedirect(sprintf('/account/commit?_=%s', $tok));
				break;
			case 'email-verify':
				$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act);
				return $RES->withRedirect(sprintf('/verify/email?_=%s', $tok));
				break;
			case 'password-reset':
				$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act);
				return $RES->withRedirect(sprintf('/account/password?_=%s', $tok));
				break;
			case 'account-init':
				$act['intent'] = 'account-open'; // Overwrite?
				$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act);
				return $RES->withRedirect(sprintf('/auth/init?_=%s', $tok));
			case 'account-open':
			case 'oauth-migrate':
				return $RES->withJSON($act);
		}

		return $this->sendFailure($RES, [
			'error_code' => 'CAO-061',
			'fail' => 'Invalid Request',
		]);

	}

}
