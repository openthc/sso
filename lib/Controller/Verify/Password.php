<?php
/**
 * Verify Password Set
 */

namespace App\Controller\Verify;

class Password extends \App\Controller\Verify\Base
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
		$sub = new \App\Controller\Account\Password($this->_container);
		$RES = $sub->post($REQ, $RES, $ARG);
		// @todo If Success
		return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
	}

}
