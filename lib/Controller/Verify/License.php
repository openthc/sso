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

		return $RES->write( $this->render('verify/license.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		var_dump($_POST);

		$LR0 = [
			'code' => $_POST['license-code'],
			'type' => $_POST['license-type'],
			'name' => ($_POST['company-name'] ?: $act['contact']['email']),
			'iso3166' => $act['iso3166'],
	];

// $RES = $RES->withAttribute('License', $LR0);
// syslog(LOG_NOTICE, )


		$_SESSION['verify']['license']['done'] = true;

		return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));


	}
}
