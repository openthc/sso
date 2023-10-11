<?php
/**
 * Verify License
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

class License extends \OpenTHC\SSO\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify License';

		$act = $this->loadTicket();
		switch ($act['intent']) {
			case 'account-invite':
				// Skip License on this one
				$_SESSION['verify']['license'] = [];
				$_SESSION['verify']['license']['done'] = true;
				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
				break;
		}

		$data['license'] = [
			'email' => $act['contact']['email'],
			'phone' => $act['contact']['phone'],
		];

		return $RES->write( $this->render('verify/license.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$LR0 = [
			'code' => $_POST['license-code'],
			'type' => $_POST['license-type'],
			'name' => ($_POST['company-name'] ?: $act['contact']['email']),
			'iso3166' => $act['iso3166'],
		];
		$_SESSION['verify']['license'] = $LR0;
		$_SESSION['verify']['license']['done'] = true;

		return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));


	}
}
