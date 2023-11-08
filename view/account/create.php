<?php
/**
 * SPDX-License-Identifier: MIT
 */

?>

<form autocomplete="off" method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="auth-wrap">

	<div class="card">
	<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
	<div class="card-body">

		<div>
			<label>Full Name</label>
			<input autocomplete="off" autofocus class="form-control" id="contact-name" name="contact-name" placeholder="- Your Full Name -" required>
		</div>

		<div class="mt-4">
			<label>Email</label>
			<input autocomplete="off" class="form-control" id="contact-email" inputmode="email" name="contact-email" placeholder="eg: user@example.com" required type="email" value="<?= __h($_SESSION['auth-open-email']) ?>">
		</div>

		<div class="mt-4">
			<label>Phone</label>
			<input autocomplete="off" class="form-control" id="contact-phone" inputmode="tel" name="contact-phone" placeholder="eg: +1 202 555 1212" required type="phone">
		</div>

	</div>

	<div class="card-footer">
		<div class="d-flex justify-content-between">
		<div>
			<button class="btn btn-primary" id="btn-account-create" name="a" type="submit" value="contact-next">Create Account <i class="icon icon-arrow-right"></i></button>
		</div>
		<div>
			<a class="btn btn-outline-secondary" href="/auth/open?service=<?= __h($_GET['service']) ?>">Sign In</a>
		</div>
		</div>
	</div>

	</div>

</div>
</form>
