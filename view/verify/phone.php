
<form autocomplete="off" method="post">
<div class="auth-wrap">
<div class="card">

<h1 class="card-header"><?= $data['Page']['title'] ?></h1>

<div class="card-body">

	<?php
	if ($data['verify_phone_warn']) {
		printf('<div class="alert alert-warning">%s</div>', h($data['verify_phone_warn']));
	}
	?>

	<div class="form-group">
		<label>Phone</label>
		<input
			autocomplete="off"
			<?= ($data['verify_phone_code'] ? 'autofocus' : '') ?>
			class="form-control"
			id="contact-phone"
			inputmode="tel"
			name="contact-phone"
			placeholder="- your phone number -"
			required
			type="tel"
			value="<?= h($data['contact_phone']) ?>">
		<p>Please include your country code</p>
	</div>

	<?php
	if ($data['verify_phone_code']) {
	?>
		<div class="form-group">
			<label>Verification Code:</label>
			<div class="input-group">
				<input autofocus class="form-control" id="phone-verify-code" name="phone-verify-code" value="">
			</div>
			<p>You may need to wait a few minutes for the message to arrive. You can resend if needed.</p>
		</div>
	<?php
	}
	?>


</div>

<div class="card-footer r">
	<?php
	if (empty($data['verify_phone_code'])) {
		echo ' <button class="btn btn-primary" name="a" type="submit" value="phone-verify-send">Send Confirmation <i class="icon icon-arrow-right"></i></button>';
	} else {
		echo ' <button class="btn btn-outline-secondary" name="a" type="submit" value="phone-verify-send">Resend <i class="icon icon-arrow-right"></i></button>';
		echo ' <button class="btn btn-primary" name="a" type="submit" value="phone-verify-save">Confirm <i class="icon icon-arrow-right"></i></button>';
		if ($data['verify_phone_tick'] > 2) {
			echo ' <button class="btn btn-outline-warning" name="a" type="submit" value="phone-verify-skip">Skip</button>';
		}
	}
	?>
</div>

</div>
</div>
</form>
