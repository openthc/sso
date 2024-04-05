<?php
/**
 * End of the Line
 *
 * SPDX-License-Identifier: MIT
 */

if ( ! empty($data['error_code'])) {
	$data['Page']['title'] = sprintf('Error: %s', $data['error_code']);
	switch ($data['error_code']) {
		case 'CAC-065':
		case 'CAC-083':
		case 'CAC-086':
			$data['Page']['title'] = 'Account Exists';
			$data['warn'] = 'You have already created an account, sign in to that one';
			$data['foot'] = '<div class="r"><a class="btn btn-outline-primary" href="/auth/open">Sign In <i class="icon icon-arrow-right"></i></a></div>';
			break;
		case 'CAC-111':
			$data['Page']['title'] = 'Account Confirmation';
			$data['info'] = 'Please check your email to confirm your account.';
			$data['body'] = <<<HTML
			<p>We have just sent you an email, with the next steps.<p>
			<p>You will need to confirm your request through a link in that message and then complete the verification process.</p>
			<p>The message will expire in 15 minutes.</p>
			HTML;
			break;
		case 'CAO-100':
			$data['Page']['title'] = 'Check Your Inbox';
			$data['body'] = '<p>If the email address submitted was valid and has an account then an email should arrive shortly with password reset instructions.</p><p>Check your mailbox (or SPAM folder) for this message and follow the steps indicated.</p><hr><p>Contact <em><a href="mailto:help@openthc.com">help@openthc.com</a></em> if you need additional assistance</p>';
			break;
		case 'CVB-030':
			$data['fail'] = 'Invalid Request';
			break;
		case 'CAO-040':
			$data['fail'] = 'Invalid Request, Token Expired or Invalid';
			break;
		case 'CVM-119':
			$data['Page']['title'] = 'Verification Complete';
			$data['body'] = <<<HTML
			<h2 class="alert alert-success">Your Account has been Verified and Activated.</h2>
			<p>You may now sign in.</p>
			HTML;
			$data['foot'] = <<<HTML
			<div class="d-flex justify-content-between">
				<a class="btn btn-primary" href="/auth/open?service={$data['service']}">Sign In <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
			</div>
			HTML;
			break;
		case 'CVM-130':
			$data['Page']['title'] = 'Verification Complete';
			$data['body'] = <<<HTML
			<h2 class="alert alert-success">Account Pending Activation.</h2>
			<p>You will soon receive an activation email which will complete the account creation process.</p>
			HTML;
			$data['foot'] = <<<HTML
			<div class="d-flex justify-content-between">
			<a class="btn btn-primary" href="/profile" tabindex="1">View Profile</a>
			<a class="btn btn-outline-danger" href="https://openthc.com/help" tabindex="2" target="_blank">Get Help <i class="fas fa-life-ring"></i></a>
			</div>
			HTML;
			break;
		default:
			$data['Page']['title'] = sprintf('Error: %s', $data['error_code']);
	}
}

?>

<div class="auth-wrap">

	<div class="card">
	<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
	<div class="card-body">

		<?php
		if ($data['fail']) {
			printf('<div class="alert alert-danger">%s</div>', __h($data['fail']));
		}

		if ($data['warn']) {
			printf('<div class="alert alert-warning">%s</div>', __h($data['warn']));
		}

		if ($data['info']) {
			printf('<div class="alert alert-info">%s</div>', __h($data['info']));
		}

		echo $data['body'];

		// It's the Secret Token
		if ( ! empty($_GET['t'])) {
			echo '<div id="alert-test-link">';
			echo sprintf('<hr><div class="alert alert-warning">Auth: <a href="/auth/once?_=%s">SSO/auth/once</a></div>', rawurlencode($_GET['t']));
			echo '</div>';
		}

		?>

	</div>
	<?php
	if ($data['foot']) {
		printf('<div class="card-footer">%s</div>', $data['foot']);
	}
	?>
	</div>
</div>
