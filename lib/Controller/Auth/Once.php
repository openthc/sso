<?php
/**
 * One Time Link Handler
 */

namespace App\Controller\Auth;

use App\Contact;

class Once extends \App\Controller\Base
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
				'e' => 'cao066'
			]));
		}
		$act = json_decode($chk['meta'], true);
		if (empty($act)) {
			$dbc_auth->query('DELETE FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			return $RES->withRedirect('/done?e=cao077');
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
	private function accountCreate($RES, $data)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// Update Contact, Promote Email to Username
		$chk = $dbc_auth->fetchOne('SELECT id FROM auth_contact WHERE username = :u0', [ ':u0' => $data['contact']['email'] ]);
		if (empty($chk)) {
			__exit_text('Invalid Account [CAO-079]', 400);
		}
		$data['contact']['username'] = $data['contact']['email'];

		// Log It
		$dbc_auth->insert('log_event', [
			'contact_id' => $data['contact']['id'],
			'code' => 'Contact/Account/Create',
			'meta' => json_encode($data),
		]);

		$dbc_auth->query('BEGIN');
		$dbc_main->query('BEGIN');

		// Update Auth Contact
		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $data['contact']['id'],
			':f1' => \App\Contact::FLAG_EMAIL_GOOD | \App\Contact::FLAG_PHONE_WANT,
		];
		$dbc_auth->query($sql, $arg);

		// Update Base Contact
		$sql = 'UPDATE contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $data['contact']['id'],
			':f1' => \App\Contact::FLAG_EMAIL_GOOD | \App\Contact::FLAG_PHONE_WANT,
		];
		$dbc_main->query($sql, $arg);

		$dbc_auth->query('COMMIT');
		$dbc_main->query('COMMIT');

		// Next Step
		$data['intent'] = 'account-verify';
		$act = new \App\Auth_Context_Ticket($dbc_auth);
		$act->create($data);

		return $RES->withRedirect(sprintf('/verify?_=%s', $act['id']));

	}

}
