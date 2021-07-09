
<form autocomplete="off" method="post">
<div class="auth-wrap">
<div class="card">

<h1 class="card-header"><?= $data['Page']['title'] ?></h1>

<div class="card-body">

	<div class="form-group">
		<label>Email</label>
		<div class="input-group">
			<input autocomplete="off" class="form-control" id="contact-email" inputmode="email" name="contact-email" placeholder="eg: you@example.com" required type="email" value="<?= h($data['Contact']['email']) ?>">
			<div class="input-group-append">
				<div class="input-group-text">
					<?= (\App\Contact::FLAG_EMAIL_GOOD & $data['Contact']['flag'] ? 'OK' : '-??-' ) ?>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="card-footer r">
	<?php
	if ($data['verify_email']) {
		echo '<button class="btn btn-primary" name="a" type="submit" value="verify-email-save">Confirm <i class="icon icon-arrow-right"></i></button>';
	} else {
	?>
		<button class="btn btn-outline-secondary" name="a" type="submit" value="email-verify-send">Resend Confirmation <i class="icon icon-arrow-right"></i></button>
		<button class="btn btn-primary" name="a" type="submit" value="verify-email-save">Confirm <i class="icon icon-arrow-right"></i></button>
	<?php
	}
	?>
</div>

</div>
</div>
</form>
