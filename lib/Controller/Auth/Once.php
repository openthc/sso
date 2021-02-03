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
		$tmp = $dbc_auth->fetchOne('SELECT meta FROM auth_context_ticket WHERE id = :t', [ ':t' => $_GET['_']]);
		if (empty($tmp)) {
			return $RES->withRedirect('/done?e=cao066');
		}
		$act = json_decode($tmp, true);
		if (empty($act)) {
			$dbc_auth->query('DELETE FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			return $RES->withRedirect('/done?e=cao077');
		}

		switch ($act['intent']) {
			case 'account-create':
				return $this->accountCreate($RES, $act);
				break;
			case 'email-verify':

				$arg = [
					'action' => 'email-verify-save',
					'contact' => $act['contact'],
				];
				$arg = json_encode($arg);
				$arg = _encrypt($arg, $_SESSION['crypt-key']);

				return $RES->withRedirect('/account/verify?_=' . $arg);

				break;

			case 'password-reset':

				$val = [
					'contact' => $act['contact'],
				];
				$val = json_encode($val);
				$x = _encrypt($val, $_SESSION['crypt-key']);

				return $RES->withRedirect('/account/password?_=' . $x);

				break;

			case 'init':
			case 'oauth-migrate':
				// OK
				return $RES->withJSON($act);
		}

		$data = $this->data;

		// if (strtotime($act['ts_expires']) < $_SERVER['REQUEST_TIME']) {
			// $dbc->query('DELETE FROM auth_context_ticket WHERE id = :t0', $act['id']);
			// __exit_html('<h1>Invalid Ticket [CAO#028]</h2><p>The link you followed has expired</p>', 400);
		// }

		$data['Page']['title'] = 'Error';
		$RES = $this->_container->view->render($RES, 'page/done.html', $data);
		return $RES->withStatus(400);

	}

	/**
	 * Account Create Confirm
	 */
	private function accountCreate($RES, $data)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// Update Contact
		$email = $data['contact']['email'];
		$chk = $dbc_auth->fetchOne('SELECT id FROM auth_contact WHERE username = :u0', [ ':u0' => $email ]);
		if (empty($chk)) {
			__exit_text('Invalid [CAO-073]', 400);
		}

		// Log It
		$dbc_auth->insert('log_event', [
			'company_id' => $data['company']['id'],
			'contact_id' => $data['contact']['id'],
			'code' => 'Contact/Account/Create',
			'meta' => json_encode($data),
		]);

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

		// Next Step
		$data['intent'] = 'account-confirm-verify';
		$act = new \App\Auth_Context_Ticket($dbc_auth);
		$act->create($data);

		return $RES->withRedirect(sprintf('/account/verify?_=%s', $act['id']));

	}

}
