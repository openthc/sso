<?php
/**
 * One Time Link Handler
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Auth;

use OpenTHC\SSO\Auth_Contact;

class Once extends \OpenTHC\SSO\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Token Links
		if (empty($_GET['_'])) {
			__exit_text('Invalid Request [CAO-016]', 400);
		}

		if (!preg_match('/^([\w\-]{32,128})$/i', $_GET['_'], $m)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Request [CAO-022]' ]
			], 400);
		}

		$dbc_auth = $this->_container->DBC_AUTH;
		$chk = $dbc_auth->fetchRow('SELECT expires_at, meta FROM auth_context_ticket WHERE id = :t', [ ':t' => $_GET['_']]);
		if (empty($chk['meta'])) {
			$dbc_auth->query('DELETE FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			return $RES->withRedirect('/done?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAO-066'
			]));
		}
		$act = json_decode($chk['meta'], true);
		if (empty($act)) {
			$dbc_auth->query('DELETE FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			return $RES->withRedirect('/done?e=CAO-077');
		}

		// if (strtotime($act['expires_at']) < $_SERVER['REQUEST_TIME']) {
		// 	$dbc->query('DELETE FROM auth_context_ticket WHERE id = :t0', $act['id']);
		// 	__exit_html('<h1>Invalid Ticket [CAO-028]</h2><p>The link you followed has expired</p>', 400);
		// }

		// Intention Router
		switch ($act['intent']) {
			case 'account-create':
				return $this->accountCreate($RES, $act);
				break;
			case 'email-verify':
				return $RES->withRedirect(sprintf('/verify/email?_=%s', $_GET['_']));
				break;
			case 'password-reset':
				return $RES->withRedirect('/account/password?_=' . $_GET['_']);
				break;
			case 'account-open':
			case 'oauth-migrate':
				return $RES->withJSON($act);
		}

		$data = $this->data;

		$data['Page']['title'] = 'Error';
		$data['body'] = '<div class="alert alert-danger">Invalid Request [CAO-061]</div>';

		$RES = $RES->write( $this->render('done.php', $data) );
		$RES = $RES->withStatus(400);

		return $RES;

	}

	/**
	 * Account Create Confirm
	 */
	private function accountCreate($RES, $act_data)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// Update Contact, Promote Email to Username
		$chk = $dbc_auth->fetchOne('SELECT id, flag, stat FROM auth_contact WHERE id = :c0', [ ':c0' => $act_data['contact']['id'] ]);
		if (empty($chk)) {
			__exit_text('Invalid Account [CAO-079]', 400);
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
		$ct_auth = new Auth_Contact($dbc_auth, $act_data['contact']);
		$ct_auth['username'] = $act_data['contact']['email'];
		$ct_auth['password'] = null;
		$ct_auth->setFlag(\OpenTHC\Contact::FLAG_EMAIL_GOOD | \OpenTHC\Contact::FLAG_PHONE_WANT);
		$ct_auth->save();

		// Update Base Contact
		$ct_main = new \OpenTHC\Contact($dbc_main, $act_data['contact']);
		$ct_main->setFlag(\OpenTHC\Contact::FLAG_EMAIL_GOOD | \OpenTHC\Contact::FLAG_PHONE_WANT);
		$ct_main->save();

		$dbc_auth->query('COMMIT');
		$dbc_main->query('COMMIT');

		// Init with this same token
		return $RES->withRedirect(sprintf('/auth/init?_=%s', $_GET['_']));

	}

}
