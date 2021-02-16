<?php
/**
 * Verify a Contact Profile
 */

namespace App\Controller\Account;

use App\Contact;

class Verify extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();

		// Load Contact (from ticket, no DB?)
		$sql = <<<SQL
SELECT auth_contact.id, auth_contact.flag, auth_contact.username
FROM auth_contact
WHERE auth_contact.id = :c0
SQL;
		$arg = [
			':c0' => $act['contact']['id']
		];
		$Contact = $this->_container->DBC_AUTH->fetchRow($sql, $arg);
		if (empty($Contact['id'])) {
			_err_exit_html('Invalid Request [CAV-037]', 400);
		}
		$Contact_Base = $this->_container->DBC_MAIN->fetchRow('SELECT id, email, phone FROM contact WHERE id = :c0', $arg);
		if (empty($Contact_Base['id'])) {
			_err_exit_html('Invalid Request [CAV-040]', 400);
		}

		switch ($act['intent']) {
		case 'email-verify-save':
			return $this->emailVerifyConfirm($RES, $act);
		case 'account-create':
		case 'account-create-verify':
			// OK
			break;
		default:
			_err_exit_html('Invalid Request [CAV-042]', 400);
		}

		// Output Data
		$data = $this->data;
		$data['Page']['title'] = 'Account Verification';
		$data['Contact'] = $Contact;
		$data['contact_email'] = $Contact['username'];
		$data['contact_phone'] = $Contact_Base['phone'];
		if (!empty($_SESSION['verify']['phone']['e164'])) {
			$data['contact_phone'] = $_SESSION['verify']['phone']['e164'];
		}

		$data['verify_email'] = (0 == ($Contact['flag'] & Contact::FLAG_EMAIL_GOOD));
		$data['verify_phone'] = (0 == ($Contact['flag'] & Contact::FLAG_PHONE_GOOD));
		$data['verify_password'] = (strlen($Contact['password']) > 0);

		$file = 'page/account/verify.html';

		if (!empty($data['verify_email'])) {
			return $this->_container->view->render($RES, $file, $data);
		}

		// Prompt for Phone Verify

		if (empty($_SESSION['verify']['phone']['done']) && !empty($data['verify_phone'])) {

			$data['verify_phone_tick'] = $_SESSION['verify']['phone']['tick'];

			if (!empty($_SESSION['verify']['phone']['code'])) {
				$data['verify_phone_code'] = true;
			}

			$data['verify_phone_warn'] = $_SESSION['verify']['phone']['warn'];

			return $this->_container->view->render($RES, $file, $data);

		}

		if (empty($Contact['password']) || ('NONE' == substr($Contact['password'], 0, 4))) {
			// Needs Password Reset
			// __exit_text('Password Reset Here, Redirect to ./password', 501);
			return $RES->withRedirect(sprintf('/account/password?_=%s', $_GET['_']));
		}

		__exit_text('Invalid Request [CAV-087]', 400);

		// It's good, so send to INIT
		// if (empty($data['verify_email']) && empty($data['verify_phone'])) {

		// 	unset($_SESSION['verify']['phone']);

		// 	$act['intent'] = 'account-open';
		// 	$act_next = new \App\Auth_Context_Ticket($this->_container->DBC_AUTH);
		// 	$act_next->create($act);

		// 	return $RES->withRedirect(sprintf('/auth/init?_=%s', $act_next['id']));

		// }

	}

	/**
	 * POST Handler
	 */
	function post($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();

		switch ($_POST['a']) {
		case 'email-verify-send':

			return $this->emailVerifySend($RES, $act);

		case 'phone-verify-send':

			return $this->phoneVerifySend($RES, $act);

		case 'phone-verify-save':
		case 'phone-verify-skip':

			// Skip, if Tick Count Enough
			if ('phone-verify-skip' == $_POST['a']) {
				if ($_SESSION['verify']['phone']['tick'] > 1) {

					// Fake Something?
					$_SESSION['verify']['phone'] = [
						'done' => true
					];

					return $RES->withRedirect(sprintf('/account/verify?_=%s', $_GET['_']));

				}
			}

			return $this->phoneVerifySave($RES, $act);

		}

		// Not Handled
		$data = $this->data;
		$data['Page']['title'] = 'Error';
		$data['body'] = '<div class="alert alert-danger">Invalid Request [CAV-143]</div>';
		$RES = $this->_container->view->render($RES, 'page/done.html', $data);
		return $RES->withStatus(400);

	}

	/**
	 *
	 */
	function emailVerifyConfirm($RES, $ARG)
	{
		$dbc = $this->_container->DBC_AUTH;

		// Set Flag
		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':f1' => Contact::FLAG_EMAIL_GOOD,
		];
		$dbc->query($sql, $arg);

		// Del Flag
		$sql = 'UPDATE auth_contact SET flag = flag & ~:f0::int WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':f0' => Contact::FLAG_EMAIL_WANT,
		];
		$dbc->query($sql, $arg);

		$data = $this->data;
		$data['Page']['title'] = 'Email Verification';
		$data['info'] = 'Email address has been validated';
		if (empty($_SESSION['Contact'])) {
			$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/open">Sign In <i class="icon icon-arrow-right"></i></a></div>';
		} else {
			$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/init">Continue <i class="icon icon-arrow-right"></i></a></div>';
		}

		// Set Contact Model on Response
		$RES = $RES->withAttribute('Contact', [
			'id' => $ARG['contact']['id'],
			'username' => $ARG['contact']['username'],
			'flag' => Contact::FLAG_EMAIL_GOOD,
		]);

		// @deprecated use ACT, is this even the right spot for it?
		// Landed here from Password Reset?
		// No prompt, just show verifications
		if ('password-reset' == $ARG['source']) {
			unset($ARG['intent']);
			unset($ARG['source']);
			$x = _encrypt(json_encode($ARG), $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/verify?_=' . $x);
		}

		return $this->_container->view->render($RES, 'page/done.html', $data);

	}

	/**
	 * Save Phone Verification
	 */
	function phoneVerifySave($RES, $ARG)
	{
		$_POST['phone-verify-code'] = strtoupper($_POST['phone-verify-code']);

		// Code does not match
		if ($_SESSION['verify']['phone']['code'] != $_POST['phone-verify-code']) {

			unset($_SESSION['verify']['phone']['code']);
			unset($_SESSION['verify']['phone']['warn']);

			return $RES->withRedirect('/account/verify?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'cav205'
			]));

		}

		$dbc_auth = $this->_container->DBC_AUTH;

		// Set Flag on Auth Contact
		$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':f1' => Contact::FLAG_PHONE_GOOD,
		];
		$dbc_auth->query($sql, $arg);

		// Clear Flag on Auth Contact
		$sql = 'UPDATE auth_contact SET flag = flag & ~:f0::int WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':f0' => Contact::FLAG_PHONE_WANT,
		];
		$dbc_auth->query($sql, $arg);

		// Update Status
		$dbc_auth->query('UPDATE auth_contact SET stat = 200 WHERE id = :pk AND stat = 100 AND flag & :f1 != 0', [
			':pk' => $ARG['contact']['id'],
			':f1' => Contact::FLAG_EMAIL_GOOD | Contact::FLAG_PHONE_GOOD
		]);

		// Update Phone on Base Contact
		$dbc_main = $this->_container->DBC_MAIN;
		$sql = 'UPDATE contact SET phone = :p0, stat = 200, flag = :f1 WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':p0' => $_SESSION['verify']['phone']['e164'],
			':f1' => Contact::FLAG_EMAIL_GOOD | Contact::FLAG_PHONE_GOOD
		];
		$dbc_main->query($sql, $arg);

		unset($_SESSION['verify']['phone']);

		return $RES->withRedirect(sprintf('/account/verify?_=%s', $_GET['_']));

	}

	/**
	 *
	 */
	function emailVerifySend($RES, $ARG)
	{
		$dbc = $this->_container->DBC_AUTH;

		$acs = [];
		$acs['id'] = _random_hash();
		$acs['meta'] = json_encode([
			'intent' => 'email-verify',
			'contact' => $ARG['contact'],
		]);
		$dbc->insert('auth_context_ticket', $acs);

		// Return/Redirect
		$ret_path = '/done';
		$ret_args = [
			'e' => 'cav228'
		];

		// Test Mode
		if ($_ENV['test']) {

			$ret_args['r'] = '/auth/once';
			$reg_args['a'] = $acs['id'];

		} else {

			$arg = [];
			$arg['to'] = $ARG['contact']['email'];
			$arg['file'] = 'sso/contact-email-verify.tpl';
			$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
			$arg['data']['mail_subject'] = 'Email Verification';
			$arg['data']['auth_context_ticket'] = $acs['id'];

			try {

				$cic = new \OpenTHC\Service\OpenTHC('cic');
				$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);

				if (201 == $res['code']) {
					$ret_args['s'] = 't';
				}

			} catch (Exception $e) {
				$ret_args['e'] = 'cav255';
				$ret_args['s'] = 'f';
			}

		}

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}

	/**
	 * Send the Phone Verification Text
	 */
	function phoneVerifySend($RES, $ARG)
	{
		unset($_SESSION['verify']['phone']['warn']);

		$_SESSION['verify']['phone'] = [
			'tick' => intval($_SESSION['verify']['phone']['tick']) + 1,
			'code' => substr(str_shuffle('ADEFHJKMNPRTWXY34679'), 0, 6),
			'e164' => _phone_e164($_POST['contact-phone'])
		];

		$ret_path = '/account/verify';
		// $ret_path = $_SERVER['HTTP_REFERER'];
		// $ret_path = strtok($ret_path, '?');
		$ret_args = [
			'_' => $_GET['_'],
		];

		// Test Mode
		if ($_ENV['test']) {

			$ret_args['c'] = $_SESSION['verify']['phone']['code'];

		} else {

			$arg = [];
			$arg['target'] = $_SESSION['verify']['phone']['e164'];
			$arg['body'] = sprintf('Account Verification Code: %s', $_SESSION['verify']['phone']['code']);

			try {

				$cic = new \OpenTHC\Service\OpenTHC('cic');
				$res = $cic->post('/api/v2018/phone/send', [ 'form_params' => $arg ]);
				switch ($res['code']) {
					case 200:
						$ret_args['e'] = 'cav294';
						$ret_args['s'] = 't'; // Send=True
						break;
					case 500:
					default:
						$ret_args['e'] = 'cav297';
						$ret_args['s'] = 'f'; // Send=False
						unset($_SESSION['verify']['phone']['code']);
						unset($_SESSION['verify']['phone']['e164']);
						$_SESSION['verify']['phone']['warn'] = 'Double check this number and try again';
						break;
				}

			} catch (Exception $e) {
				$ret_args['e'] = 'cav304';
				$reg_args['s'] = 'e'; // Exception Notice
			}

		}

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}

	/**
	 * Load our Ticket
	 */
	function loadTicket()
	{
		$act = new \App\Auth_Context_Ticket($this->_container->DBC_AUTH);
		$act->loadBy('id', $_GET['_']);
		if (empty($act['id'])) {
			_err_exit_html('Invalid Request [CAV-356]', 400);
		}

		$act = json_decode($act['meta'], true);
		if (empty($act)) {
			_err_exit_html('Invalid Request [CAV-360]', 400);
		}

		if (empty($act['contact']['id'])) {
			_err_exit_html('Invalid Request [CAV-365]', 400);
		}

		switch ($act['intent']) {
			case 'account-create-verify':
				// OK
			break;
			default:
				_err_exit_html('Invalid Request [CAV-374]', 400);
		}

		// Init Verification Data
		if (empty($_SESSION['verify'])) {
			$_SESSION['verify'] = [
				'email' => [],
				'phone' => [],
				'password' => [],
			];
		}

		return $act;
	}
}
