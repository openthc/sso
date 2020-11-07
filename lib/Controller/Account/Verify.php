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
		$ARG = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$ARG = json_decode($ARG, true);

		if (empty($ARG)) {
			__exit_text('Invalid Request [CAV#018]', 400);
		}

		if (empty($ARG['contact']['id'])) {
			__exit_text('Invalid Request [CAV#022]', 400);
		}

		// Load Contact
		$sql = <<<SQL
SELECT auth_contact.id, auth_contact.flag, auth_contact.username
FROM auth_contact
WHERE auth_contact.id = :c0
SQL;
		$arg = [
			':c0' => $ARG['contact']['id']
		];
		$Contact = $this->_container->DBC_AUTH->fetchRow($sql, $arg);
		if (empty($Contact['id'])) {
			__exit_text('Invalid Request [CAV#037]', 400);
		}
		$Contact_Base = $this->_container->DBC_MAIN->fetchRow('SELECT id, email, phone FROM contact WHERE id = :c0', $arg);
		if (empty($Contact_Base['id'])) {
			var_dump($arg); exit;
			__exit_text('Invalid Request [CAV#040]', 400);
		}


		switch ($ARG['action']) {
		case 'email-verify-save':
			return $this->emailVerifyConfirm($RES, $ARG);
		}

		$file = 'page/account/verify.html';

		$data = $this->data;
		$data['Page']['title'] = 'Account Verification';
		$data['Contact'] = $Contact;
		$data['contact_email'] = $Contact['username'];
		$data['contact_phone'] = $Contact_Base['phone'];
		if (!empty($_SESSION['phone-verify-e164'])) {
			$data['contact_phone'] = $_SESSION['phone-verify-e164'];
		}

		if (0 == ($Contact['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			$data['verify_email'] = true;
		}

		if (0 == ($Contact['flag'] & Contact::FLAG_PHONE_GOOD)) {
			$data['verify_phone'] = true;
		}

		if ($data['verify_phone']) {

			if (!empty($_SESSION['phone-verify-code'])) {
				$data['verify_phone_code'] = true;
			}

			$data['verify_phone_warn'] = $_SESSION['phone-verify-warn'];

		}

		// It's good, so send to INIT
		if (empty($data['verify_email']) && empty($data['verify_phone'])) {
			return $RES->withRedirect('/auth/init');
		}

		return $this->_container->view->render($RES, $file, $data);

	}

	function post($REQ, $RES, $ARG)
	{
		$ARG = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$ARG = json_decode($ARG, true);

		if (empty($ARG)) {
			__exit_text('Invalid Request [CAP#055]', 400);
		}

		if (empty($ARG['contact'])) {
			__exit_text('Invalid Request [CAP#059]', 400);
		}

		switch ($_POST['a']) {
		case 'email-verify-send':

			return $this->emailVerifySend($RES, $ARG);

		case 'phone-verify-send':

			return $this->phoneVerifySend($RES, $ARG);

		case 'phone-verify-save':

			return $this->phoneVerifySave($RES, $ARG);

		}

		$data = $this->data;
		$data['Page']['title'] = 'Error';
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

		// Landed here from Password Reset?
		// No prompt, just show verifications
		if ('password-reset' == $ARG['source']) {
			unset($ARG['action']);
			unset($ARG['source']);
			$x = _encrypt(json_encode($ARG), $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/verify?_=' . $x);
		}

		return $this->_container->view->render($RES, 'page/done.html', $data);

	}

	function phoneVerifySave($RES, $ARG)
	{
		$_POST['phone-verify-code'] = strtoupper($_POST['phone-verify-code']);
		if ($_SESSION['phone-verify-code'] == $_POST['phone-verify-code']) {

			$dbc = $this->_container->DBC_AUTH;

			// Is Good
			$sql = 'UPDATE auth_contact SET flag = flag | :f1 WHERE id = :pk';
			$arg = [
				':pk' => $ARG['contact']['id'],
				':f1' => Contact::FLAG_PHONE_GOOD,
			];
			$dbc->query($sql, $arg);

			$sql = 'UPDATE auth_contact SET flag = flag & ~:f0::int WHERE id = :pk';
			$arg = [
				':pk' => $ARG['contact']['id'],
				':f0' => Contact::FLAG_PHONE_WANT,
			];
			$dbc->query($sql, $arg);

			// Update Phone
			$dbc_main = $this->_container->DBC_MAIN;
			$sql = 'UPDATE contact SET phone = :p0 WHERE id = :pk';
			$arg = [
				':pk' => $ARG['contact']['id'],
				':p0' => $_SESSION['phone-verify-e164'],
			];
			$dbc_main->query($sql, $arg);

			$data = $this->data;
			$data['Page']['title'] = 'Phone Verification';
			$data['info'] = 'Phone Number has been validated';
			$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/init">Continue <i class="icon icon-arrow-right"></i></a></div>';

			// Set Contact Model on Response
			$RES = $RES->withAttribute('Contact', [
				'id' => $ARG['contact']['id'],
				'username' => $ARG['contact']['username'],
				'flag' => Contact::FLAG_PHONE_GOOD,
			]);

			return $this->_container->view->render($RES, 'page/done.html', $data);

		}

	}

	/**
	 *
	 */
	function emailVerifySend($RES, $ARG)
	{
		$dbc = $this->_container->DB;

		$acs = [];
		$acs['id'] = _random_hash();
		$acs['meta'] = json_encode([
			'action' => 'email-verify',
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

			$ret_args['r'] = "https://{$_SERVER['SERVER_NAME']}/auth/once";
			$reg_args['a'] = $acs['id'];

		} else {

			$arg = [];
			$arg['to'] = $ARG['contact']['email'];
			$arg['file'] = 'sso/contact-email-verify.tpl';
			$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
			$arg['data']['mail_subj'] = 'Email Verification';
			$arg['data']['auth_context_ticket'] = $acs['id']; // v1
			$arg['data']['auth_context_token'] = $acs['id']; // v0

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
		unset($_SESSION['phone-verify-warn']);

		$_SESSION['phone-verify-code'] = substr(str_shuffle('ADEFHJKMNPRTWXY34679'), 0, 6);
		$_SESSION['phone-verify-e164'] = _phone_e164($_POST['contact-phone']);

		$ret_path = $_SERVER['HTTP_REFERER'];
		$ret_path = strtok($ret_path, '?');
		$ret_args = [
			'_' => $_GET['_'],
		];

		// Test Mode
		if ($_ENV['test']) {

			$ret_args['c'] = $_SESSION['phone-verify-code'];

		} else {

			$arg = [];
			$arg['target'] = $_SESSION['phone-verify-e164'];
			$arg['body'] = sprintf('Account Verification Code: %s', $_SESSION['phone-verify-code']);

			try {

				$cic = new \OpenTHC\Service\OpenTHC('cic');
				$res = $cic->post('/api/v2018/phone/send', [ 'form_params' => $arg ]);
				if (200 == $res['code']) {
					$ret_args['e'] = 'cav294';
					$ret_args['s'] = 't'; // Send=True
				} else {
					$ret_args['e'] = 'cav297';
					$ret_args['s'] = 'f'; // Send=False
					$_SESSION['phone-verify-code'] = null;
					$_SESSION['phone-verify-e164'] = null;
					$_SESSION['phone-verify-warn'] = 'Double check this number and try again';
				}

			} catch (Exception $e) {
				$ret_args['e'] = 'cav304';
				$reg_args['s'] = 'e'; // Exception Notice
			}

		}

		return $RES->withRedirect($ret_path . '?' . http_build_query($ret_args));

	}
}
