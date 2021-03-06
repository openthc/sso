<?php
/**
 * Done w/Message
 */

namespace App\Controller;

class Done extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$file = 'page/done.html';

		$data = $this->data;
		$data['Page'] = [];
		$data['Page']['title'] = 'Done';

		if (!empty($_GET['e'])) {
			switch ($_GET['e']) {
				case 'cac065':
					$data['warn'] = 'You have already created an account, sign in to that one';
					$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/open">Sign In <i class="icon icon-arrow-right"></i></a></div>';
				break;
				case 'cac111':
					$data['Page']['title'] = 'Account Confirmation';
					$data['info'] = 'Please check your email to confirm your account.';
					$data['body'] = '<p>We have just sent you an email, with the next steps.<p><p>You will need to confirm your request through a link in that message.</p><p>Maybe you want to read more about regulations?</p><div><a class="btn btn-outline-success" href="https://openthc.com/intro">Introduction to Track and Trace <i class="icon icon-arrow-right"></i></a></div>';
				break;
				case 'cao066':
				case 'ca0077':
					$data['Page']['title'] = 'Error';
					$data['fail'] = 'The link you followed is not valid';
				break;
				case 'cao073':
					$data['Page']['title'] = 'Account Confirmed';
					$data['info'] = 'Account Confirmed';
					$data['body'] = '<p>Thank you, your email has been verified and your account request confirmed.</p><p>Next, you will need to set a password.</p>';
					$data['foot'] = sprintf('<div class="r"><a class="btn btn-outline-success" href="/account/password?_=%s">Set Password <i class="icon icon-arrow-right"></i></a></div>', $_GET['_']);
				break;
				case 'cao100':
					$data['Page']['title'] = 'Check Your Inbox';
					$data['body'] = '<p>If the email address submitted was valid and has an account then an email should arrive shortly with password reset instructions.</p><p>Check your mailbox (or SPAM folder) for this message and follow the steps indicated.</p><hr><p>Contact <em><a href="mailto:help@openthc.com">help@openthc.com</a></em> if you need additional assistance</p>';
				break;
				case 'cav228':
					$data['Page']['title'] = 'Email Verification';
					$data['body'] = '<div class="alert alert-success">Check Your Inbox!</div><p>Your your inbox for a message from us, there is a link we want you to click.</p>';
				break;
				case 'cav255':
				case 'cac190':
					$data['Page']['title'] = 'Email Verification';
					$data['body'] = '<div class="alert alert-danger">Email Verification Send Failure [CAV-255]</div><p>Please contact support</p>';
				break;
			}
		}

		return $RES->write( $this->render('done.php', $data) );

	}
}
