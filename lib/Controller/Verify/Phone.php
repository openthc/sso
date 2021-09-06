<?php
/**
 * Verify Phone
 */

namespace App\Controller\Verify;

use OpenTHC\Contact;

class Phone extends \App\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Phone';

		$act = $this->loadTicket();

		$data['Contact'] = $act['contact'];
		// $data['Contact']['phone'] = $data['Contact']['username'];

		$data['contact_phone'] = $Contact_Base['phone'];
		if (!empty($_SESSION['verify']['phone']['e164'])) {
			$data['contact_phone'] = $_SESSION['verify']['phone']['e164'];
		}

		$data['verify_phone'] = (0 == ($data['Contact']['flag'] & Contact::FLAG_PHONE_GOOD));

		// Prompt for Phone Verify
		if (empty($_SESSION['verify']['phone']['done']) && !empty($data['verify_phone'])) {

			$data['verify_phone_tick'] = $_SESSION['verify']['phone']['tick'];

			if (!empty($_SESSION['verify']['phone']['code'])) {
				$data['verify_phone_code'] = true;
			}

			$data['verify_phone_warn'] = $_SESSION['verify']['phone']['warn'];

		}

		return $RES->write( $this->render('verify/phone.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();

		switch ($_POST['a']) {
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

						return $RES->withRedirect(sprintf('/verify/phone?_=%s', $_GET['_']));

					}
				}

				return $this->phoneVerifySave($RES, $act);

		}

		__exit_text('Invalid Request [CVP-057]', 400);

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

			return $RES->withRedirect('/verify/phone?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'CAV-205'
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

		// Update Phone on Base Contact
		$dbc_main = $this->_container->DBC_MAIN;
		$sql = 'UPDATE contact SET phone = :p0, stat = 200, flag = :f1 WHERE id = :pk';
		$arg = [
			':pk' => $ARG['contact']['id'],
			':p0' => $_SESSION['verify']['phone']['e164'],
			':f1' => Contact::FLAG_PHONE_GOOD
		];
		$dbc_main->query($sql, $arg);

		unset($_SESSION['verify']['phone']);

		return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));

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
						$ret_args['e'] = 'CAV-294';
						$ret_args['s'] = 't'; // Send=True
						break;
					case 500:
					default:
						$ret_args['e'] = 'CAV-297';
						$ret_args['s'] = 'f'; // Send=False
						unset($_SESSION['verify']['phone']['code']);
						unset($_SESSION['verify']['phone']['e164']);
						$_SESSION['verify']['phone']['warn'] = 'Double check this number and try again';
						break;
				}

			} catch (Exception $e) {
				$ret_args['e'] = 'CAV-304';
				$reg_args['s'] = 'e'; // Exception Notice
			}

		}

		return $RES->withRedirect('/verify/phone?' . http_build_query($ret_args));

	}

}
