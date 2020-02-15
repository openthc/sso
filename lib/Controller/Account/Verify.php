<?php
/**
 * Verify a Contact Profile
 */

namespace App\Controller\Account;

use App\Contact;

class Verify extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$ARG = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$ARG = json_decode($ARG, true);

		if (empty($ARG)) {
			_exit_text('Invalid Request [CAP#019]');
		}

		if (empty($ARG['contact'])) {
			_exit_text('No [CAP#015]', 400);
		}

		switch ($ARG['action']) {
		case 'email-verify-save':
			return $this->emailVerifyConfirm($RES, $ARG);
		}
		// var_dump($ARG);
		$Contact = $ARG['contact'];

		$file = 'page/account/verify.html';

		$data = [];
		$data['Page'] = [ 'title' => 'Account Verification' ];
		$data['contact_email'] = $Contact['email'];
		$data['contact_phone'] = $Contact['phone'];
		if (!empty($_SESSION['phone-verify-e164'])) {
			$data['contact_phone'] = $_SESSION['phone-verify-e164'];
		}


		if (0 == ($Contact['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			$data['verify_email'] = true;
		}

		if (0 == ($Contact['flag'] & Contact::FLAG_PHONE_GOOD)) {
			$data['verify_phone'] = true;
		}

		if (!empty($_SESSION['phone-verify-code'])) {
			$data['verify_phone_code'] = true;
		}

		$data['verify_phone_warn'] = $_SESSION['phone-verify-warn'];

		return $this->_container->view->render($RES, $file, $data);

	}

	function post($REQ, $RES, $ARG)
	{
		$ARG = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$ARG = json_decode($ARG, true);

		if (empty($ARG)) {
			_exit_text('Invalid Request [CAP#055]', 400);
		}

		if (empty($ARG['contact'])) {
			_exit_text('Invalid Request [CAP#059]', 400);
		}

		switch ($_POST['a']) {
		case 'email-verify-send':

			return $this->emailVerifySend($RES, $ARG);

		case 'phone-verify-send':

			return $this->phoneVerifySend($RES, $ARG);

		case 'phone-verify-save':

			$_POST['phone-verify-code'] = strtoupper($_POST['phone-verify-code']);
			if ($_SESSION['phone-verify-code'] == $_POST['phone-verify-code']) {

				$dbc = $this->_container->DB;

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
				$sql = 'UPDATE contact SET phone = :p0 WHERE id = :pk';
				$arg = [
					':pk' => $ARG['contact']['id'],
					':p0' => $_SESSION['phone-verify-e164'],
				];
				$dbc->query($sql, $arg);

				$data = [];
				$data['Page']['title'] = 'Phone Verification';
				$data['info'] = 'Phone Number has been validated';
				$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/init">Continue <i class="icon icon-arrow	-right"></i></a></div>';
				return $this->_container->view->render($RES, 'page/done.html', $data);

			}

			break;
		}

		$data = [];
		$data['Page']['title'] = 'Error';
		$RES = $this->_container->view->render($RES, 'page/done.html', $data);
		return $RES->withStatus(400);

	}

	/**
	 *
	 */
	function emailVerifyConfirm($RES, $ARG)
	{
		$dbc = $this->_container->DB;

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

		$data = [];
		$data['Page']['title'] = 'Email Verification';
		$data['info'] = 'Email address has been validated';
		$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/init">Continue <i class="icon icon-arrow	-right"></i></a></div>';

		return $this->_container->view->render($RES, 'page/done.html', $data);

	}

	/**
	 *
	 */
	function emailVerifySend($RES, $ARG)
	{
		$dbc = $this->_container->DB;

		$acs = [];
		$acs['id'] = \Edoceo\Radix\ULID::generate();
		$acs['code'] = base64_encode_url(hash('sha256', openssl_random_pseudo_bytes(256), true));
		$acs['meta'] = json_encode([
			'action' => 'email-verify',
			'contact' => $ARG['contact'],
		]);
		$dbc->insert('auth_context_secret', $acs);

		$arg = [];
		$arg['to'] = $ARG['contact']['email'];
		$arg['file'] = 'sso/email-verify.tpl';
		$arg['data']['app_url'] = sprintf('https://%s', $_SERVER['SERVER_NAME']);
		$arg['data']['mail_subj'] = 'Email Verification';
		$arg['data']['once_code'] = $acs['code'];
		$cic = new \OpenTHC\Service\OpenTHC('cic');
		$res = $cic->post('/api/v2018/email/send', [ 'form_params' => $arg ]);
		if (200 == $res['code']) {
			$data = [];
			$data['Page']['title'] = 'Email Verification';
			$data['info'] = 'Check Your Inbox';
			return $this->_container->view->render($RES, 'page/done.html', $data);
		}

		_exit_text('Failure in Email', 500);
	}

	function phoneVerifySend($RES, $ARG)
	{
		unset($_SESSION['phone-verify-warn']);
		$_SESSION['phone-verify-e164'] = _phone_e164($_POST['contact-phone']);

		$_SESSION['phone-verify-code'] = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 6);

		$arg = [];
		$arg['target'] = $_SESSION['phone-verify-e164'];
		$arg['body'] = sprintf('Account Verification Code: %s', $_SESSION['phone-verify-code']);
		$cic = new \OpenTHC\Service\OpenTHC('cic');
		$res = $cic->post('/api/v2018/phone/send', [ 'form_params' => $arg ]);
		if (200 == $res['code']) {
			return $RES->withRedirect($_SERVER['HTTP_REFERER']);
		}

		$_SESSION['phone-verify-code'] = null;
		$_SESSION['phone-verify-e164'] = null;
		$_SESSION['phone-verify-warn'] = 'Double check this number and try again';
		return $RES->withRedirect($_SERVER['HTTP_REFERER']);

	}
}
