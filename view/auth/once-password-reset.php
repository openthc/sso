<?php
/**
 * SPDX-License-Identifier: MIT
 */

?>

<form method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="auth-wrap">
<div class="card">

<h1 class="card-header"><?= $data['Page']['title'] ?></h1>

<div class="card-body" style="min-height: 212px;">

	<?= $data['auth_hint'] ?>

	<div class="mt-4">
		<label>Email</label>
		<input class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" value="<?= h($data['auth_username']) ?>">
	</div>

	<?php
	if ($data['Google']['recaptcha_public']) {
	?>
		<div class="mt-4" style="min-height: 78px;">
			<div class="g-recaptcha" data-sitekey="<?= $data['Google']['recaptcha_public'] ?>"></div>
		</div>
	<?php
	}
	?>
</div>

<div class="card-footer">
	<div class="row">
		<div class="col-md-6">
			<button class="btn btn-primary" id="btn-password-reset" name="a" type="submit" value="password-reset-request">Request Password Reset</button>
		</div>
	</div>
</div>

</div>
</div>
</form>

<script src="https://www.google.com/recaptcha/api.js"></script>
