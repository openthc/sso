<?php
/**
 * Set Company Details
 *
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

	<div style="font-size: 120%;">
		<p>All accounts in the OpenTHC system are required to be linked to a company.</p>
		<p>If you do not have a company, like an individual operator, or a CO-OP you may skip this step</p>
	</div>

	<div class="mt-4">
		<label>Company Name:</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="company-name"
			name="company-name"
			placeholder="Company Name"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

	<div class="mt-4">
		<label>Government ID:</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="company-guid"
			name="company-guid"
			placeholder="Company Government ID"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

	<div class="mt-4">
		<label>Full Address:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="company-address"
			name="company-address"
			placeholder="Company Full Address"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

	<div class="mt-4">
		<label>Phone:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="company-phone"
			name="company-phone"
			placeholder="Company Phone"
			required
			tabindex="1"
			type="text"
			value="<?= __h($data['company-phone']) ?>">
	</div>

	<div class="mt-4">
		<label>Email:</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="company-email"
			name="company-email"
			placeholder="Company Email"
			required
			tabindex="1"
			type="text"
			value="<?= __h($data['company-email']) ?>">
	</div>

</div>

<div class="card-footer">
	<div class="d-flex justify-content-between">
		<div>
			<button class="btn btn-primary" name="a" tabindex="1" type="submit" value="company-save">
				Save <?= Icon::icon('save') ?>
			</button>
		</div>
		<div>
			<button class="btn btn-outline-secondary" id="btn-company-skip" name="a" tabindex="2" type="submit" value="company-skip">
				Skip <?= Icon::icon('checkbox') ?>
			</button>
		</div>
	</div>
</div>

</div>
</div>
</form>


<script>
(function() {

	var B = document.querySelector('#btn-company-skip');
	B.addEventListener('click', function() {
		var node_list = document.querySelectorAll('input[required]');
		node_list.forEach(function(n) {
			n.removeAttribute('required');
		});
	});

	<?php
	$dir_origin = \OpenTHC\Config::get('openthc/dir/origin');
	if ( ! empty($dir_origin)) {
	?>
		$('#company-name').autocomplete({
			source: '<?= $dir_origin ?>/api/autocomplete/company',
		});
	<?php
	}
	?>
})();
</script>
