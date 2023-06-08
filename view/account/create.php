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

	<!--
	<div class="mt-4">
		<label>Company Name / License ID</label>
		<input autocomplete="off" autofocus class="form-control" id="company-name" name="company-name" placeholder="- Company, Inc. -" required>
		<div>Should be your registered company name or your government issued business license ID</div>
	</div>
	-->

	<div class="mt-4">
		<label>Contact Name</label>
		<input autocomplete="off" autofocus class="form-control" id="contact-name" name="contact-name" placeholder="- Your Full Name -" required>
	</div>

	<div class="mt-4">
		<label>Email</label>
		<input autocomplete="off" class="form-control" id="contact-email" inputmode="email" name="contact-email" placeholder="eg: user@example.com" required type="email" value="<?= h($_SESSION['auth-open-email']) ?>">
	</div>

	<!--
	<div class="mt-4">
		<label>Phone</label>
		<input autocomplete="off" class="form-control" id="contact-phone" inputmode="tel" name="contact-phone" placeholder="eg: +1 202 555 1212" required type="phone">
	</div>
	-->
	</div>

	<div class="card-footer">
		<button class="btn btn-primary" id="btn-account-create" name="a" type="submit" value="contact-next">Create Account <i class="icon icon-arrow-right"></i></button>
	</div>

	</div>

</div>
</form>
