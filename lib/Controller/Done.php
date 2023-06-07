<?php
/**
 * Done w/Message
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller;

class Done extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page'] = [];
		$data['Page']['title'] = 'Done';

		if (!empty($_GET['e'])) {

			$data['error_code'] = $_GET['e'];

			switch ($_GET['e']) {
				case 'CAO-144':
					$data['Page']['title'] = 'Account Pending';
					$data['info'] = 'This account is currently: Pending';
					$data['body'] = '<p>This account must have the setup process completed and the final confirmation before you can sign in.</p>';
					break;
				case 'CAV-228':
					$data['Page']['title'] = 'Email Verification';
					$data['body'] = '<div class="alert alert-success">Check Your Inbox!</div><p>Your your inbox for a message from us, there is a link we want you to click.</p>';
					break;
				case 'CAV-255':
					$data['Page']['title'] = 'Email Verification';
					$data['body'] = '<div class="alert alert-danger">Email Verification Send Failure [CAV-255]</div><p>Please contact support</p>';
					break;
					// return $RES->write( $this->render('done-x.php', $data) );
					// $data['body'] =
			}
		}

		return $RES->write( $this->render('done.php', $data) );

	}
}
