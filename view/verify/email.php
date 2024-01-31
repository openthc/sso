<?php
/**
 * SPDX-License-Identifier: MIT
 */

use OpenTHC\SSO\UI\Icon;

?>

<form autocomplete="off" method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="auth-wrap">
<div class="card">

<h1 class="card-header"><?= $data['Page']['title'] ?></h1>

<div class="card-body">

	<div class="mt-4">
		<label>Email</label>
		<div class="input-group">
			<input autocomplete="off" class="form-control" id="contact-email" inputmode="email" name="contact-email" placeholder="eg: you@example.com" required type="email" value="<?= h($data['Contact']['email']) ?>">
			<div class="input-group-text">
				<?= (\OpenTHC\Contact::FLAG_EMAIL_GOOD & $data['Contact']['flag'] ? 'OK' : '-??-' ) ?>
			</div>
		</div>
	</div>

</div>

<div class="card-footer">
	<button class="btn btn-primary" name="a" type="submit" value="verify-email-save">Confirm <?= Icon::icon('next') ?></button>
	<?php
	if (empty($data['verify_email'])) {
	?>
		<button class="btn btn-outline-secondary" name="a" type="submit" value="email-verify-send">Resend Confirmation <?= Icon::icon('send') ?></button>
	<?php
	}
	?>
</div>

</div>
</div>
</form>
