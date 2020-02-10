<?php
/**
 * Done w/Message
 */

namespace App\Controller\Auth;

class Done extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$file = 'page/auth/done.html';

		$data = [];
		$data['Page'] = [];
		$data['Page']['title'] = 'Done';

		switch ($_GET['e']) {
		case 'cac065':
			$data['warn'] = 'You have already created an account, sign in to that one';
			break;
		case 'cac111':
			$data['Page']['title'] = 'Account Confirmation';
			$data['info'] = 'Please check your email to confirm your account';
			$data['body'] = '<p>We have just sent you an email, with the next steps.<p><p>You will need to confirm your request through a link in that email</p><p>Maybe you want to read more about regulations?</p><a class="btn btn-outline-success" href="https://openthc.com/intro">Introduction to Track and Trace &rtrif;&rtrif;</a></div>';
			break;
		case 'cao073':
			$data['Page']['title'] = 'Account Confirmed';
			$data['info'] = 'Account Confirmed';
			$data['body'] = '<p>Thank you, your email has been verified and your account request confirmed.</p><p>Next, you will need to set a password</p>';
			$data['foot'] = sprintf('<a class="btn btn-outline-success" href="/account/password?_=%s">Set Password</a>', $_SESSION['account-create']['password-args']);
			break;
		case 'cao100':
			$_ENV['title'] = 'Password Reset';
			$data['body'] = '<p>An email has been sent with reset instructions. Check your mailbox (or SPAM folder) for this message and follow the steps indicated.</p>';
			break;
		}

		return $this->_container->view->render($RES, $file, $data);

	}
}
