<?php
/**
 * Verify Password Set
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

class Password extends \OpenTHC\SSO\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Update Password';

		$act = $this->loadTicket();
		$data['auth_username'] = $act['contact']['username'];

		return $RES->write( $this->render('account/password.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$sub = new \OpenTHC\SSO\Controller\Account\Password($this->_container);
		$RES = $sub->post($REQ, $RES, $ARG);
		// @todo If Success
		return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
	}

}
