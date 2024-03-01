<?php
/**
 * SPDX-License-Identifier: MIT
 */

$dir_origin = \OpenTHC\Config::get('openthc/dir/origin');

use OpenTHC\SSO\UI\Icon;

?>


<div class="container mt-2">

<form method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="card">
<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
<div class="card-body">

	<div class="mt-4">
		<div class="input-group mb-4">
			<label class="input-group-text">Name</label>
			<input class="form-control" id="contact-name" name="contact-name" type="text" value="<?= __h($data['Contact_Base']['name']) ?>">
			<button class="btn btn-outline-primary" name="a" value="contact-name-save"><?= Icon::icon('save') ?> Save</button>
		</div>
	</div>

	<div class="input-group mb-4">
		<label class="input-group-text">Email / Username</label>
		<input class="form-control" disabled id="contact-email" name="contact-email" readonly type="email" value="<?= __h($data['Contact_Auth']['username']) ?>">
		<button class="btn btn-outline-secondary" id="contact-email-unlock" name="a" type="button" value="contact-email-unlock"><i class="fas fa-unlock"></i> Change</button>
		<button class="btn btn-outline-secondary disabled" disabled id="contact-email-update" name="a" value="contact-email-update"><?= Icon::icon('save') ?> Save</button>
	</div>

	<div class="input-group mb-4">
		<label class="input-group-text">Password</label>
		<input class="form-control" readonly type="text" value="********">
		<button class="btn btn-outline-secondary" name="a" value="contact-password-update">Change</button>
	</div>

	<div class="input-group">
		<label class="input-group-text">Phone</label>
		<input class="form-control" name="contact-phone" readonly type="tel" value="<?= __h($data['Contact_Base']['phone']) ?>">
		<button class="btn btn-outline-secondary" name="a" value="contact-phone-update">Change</button>
	</div>

</div>
</div>

</form>


<?php
// Show Service List
if ($data['service_list']) {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Service Connections</h2>
		<div class="card-body">
			<?php
			foreach ($data['service_list'] as $s) {
			?>
				<h3><a href="<?= $s['link'] ?>" target="_blank"><?= h($s['name']) ?></a></h3>
			<?php
			}
			?>
		</div>
	</div>
<?php
} elseif ($data['service_list_default']) {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Service Connections</h2>
		<div class="card-body">
			<?php
			foreach ($data['service_list_default'] as $s) {
			?>
				<div class="mb-4">
					<h3><?= __h($s['name']) ?></h3>
					<p><?= __h($s['hint']) ?></p>
					<a class="btn btn-lg btn-outline-primary btn-service-connect"
						data-service-name="<?= __h(strtolower($s['name'])) ?>"
						href="<?= $s['link'] ?>" target="_blank">
							Open <?= __h($s['name']) ?> <?= Icon::icon('link-out') ?>
					</a>
				</div>
			<?php
			}
			?>
		</div>
	</div>

<?php
}
?>


<?php
// Show Company List
if ($data['company_list']) {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Company Connections</h2>
		<div class="card-body">
			<?php
			foreach ($data['company_list'] as $c) {
			?>
				<h3><a href="<?= sprintf('%s/company/%s', $dir_origin, $c['id']) ?>" target="_blank"><?= h($c['name']) ?></a></h3>
			<?php
			}
			?>
		</div>
		<div class="card-footer">
			<a class="btn btn-outline-secondary" href="/company/join">Join Another Company</a>
		</div>
	</div>
<?php
}
?>


</div>


<script>
$(function() {

	const $email_input = $('#contact-email');
	const $email_button = $('#contact-email-update');

	$('#contact-email-unlock').on('click', function() {

		var current_state = $email_input.attr('disabled');
		console.log(`current_state: ${current_state}`);

		if ((current_state) && ('disabled' == current_state)) {
			$email_input.removeAttr('disabled');
			$email_input.removeAttr('readonly');
			$email_input.data('original-value', $email_input.val() );
			$email_input.focus();
			$email_input.select();

			$email_button.attr('class', 'btn btn-outline-primary');
			$email_button.removeAttr('disabled');

		} else {

			$email_input.attr('disabled', 'disabled');
			$email_input.attr('readonly', 'readonly');
			$email_input.val( $email_input.data('original-value') );

			$email_button.attr('class', 'btn btn-outline-secondary disabled');
			$email_button.attr('disabled', 'disabled');

		}

	});

	$email_input.on('change keyup', function() {
		var v0 = $email_input.data('original-value');
		var v1 = this.value;
		if (v0 !== v1) {
			$email_button.attr('class', 'btn btn-primary');
		} else {
			$email_button.attr('class', 'btn btn-outline-primary');
		}
	});

});
</script>
