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
				return $this->accountCreate($RES, $act);
				break;
			case 'email-verify':
				$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act);
				return $RES->withRedirect(sprintf('/verify/email?_=%s', $tok));
				break;
			case 'password-reset':
				$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act);
				return $RES->withRedirect(sprintf('/account/password?_=%s', $tok));
				break;
			case 'account-open':
			case 'oauth-migrate':
				return $RES->withJSON($act);
		}

		return $this->sendFailure($RES, [
			'error_code' => 'CAO-061',
			'fail' => 'Invalid Request',
		]);

	}

	/**
	 * Account Create Confirm
	 */
	private function accountCreate($RES, $act_data)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// Update Contact, Promote Email to Username
		$sql = 'SELECT id, flag, stat FROM auth_contact WHERE id = :c0';
		$arg = [ ':c0' => $act_data['contact']['id'] ];
		$chk = $dbc_auth->fetchOne($sql, $arg);
		if (empty($chk)) {
			return $this->sendFailure($RES, [
				'error_code' => 'CAO-094',
				'fail' => 'Invalid Account',
			]);
		}

		// Log It (outside of transaction)
		$dbc_auth->insert('log_event', [
			'contact_id' => $act_data['contact']['id'],
			'code' => 'Contact/Account/Create',
			'meta' => json_encode($act_data),
		]);

		$dbc_auth->query('BEGIN');
		$dbc_main->query('BEGIN');

		// Update Auth Contact
		$ct_auth = new Auth_Contact($dbc_auth, $act_data['contact']['id']);
		$ct_auth->setFlag(\OpenTHC\Contact::FLAG_EMAIL_GOOD | \OpenTHC\Contact::FLAG_PHONE_WANT);
		$ct_auth->save();

		// Update Base Contact
		$ct_main = new \OpenTHC\Contact($dbc_main, $act_data['contact']['id']);
		$ct_main->setFlag(\OpenTHC\Contact::FLAG_EMAIL_GOOD | \OpenTHC\Contact::FLAG_PHONE_WANT);
		$ct_main->save();

		$dbc_auth->query('COMMIT');
		$dbc_main->query('COMMIT');

		$RES = $RES->withAttribute('Contact', [
			'id' => $act_data['contact']['id'],
			// 'username' => $act_data['contact'],
			// 'email' =>
		]);

		// Verify after Create
		$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act_data);
		return $RES->withRedirect(sprintf('/verify?_=%s', $tok));

	}

}
