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

	<p>All accounts in OpenTHC must be linked to a regulatory license.</p>
	<p>If your region or industry sector does not issue a separate license from a typical business license, you may leave this blank.</p>

	<div class="mt-4">
		<label>License Name:</label>
		<input autocomplete="off"
			autofocus
			class="form-control"
			id="license-name"
			name="licnese-name"
			placeholder="License Name"
			required
			value="<?= __h($data['company']['name']) ?>">
	</div>

	<div class="mt-4">
		<label>License Code:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="license-code"
			name="license-code"
			placeholder="License Code or Number"
			tabindex="1"
			type="text"
			value=""
		>
	</div>

	<div class="mt-4">
		<label>License Type:</label>
		<select
			autocomplete="off"
			class="form-control"
			id="license-type"
			name="license-type"
			tabindex="1"
			type="text"
			value=""
		>
			<option>Grower</option>
			<option>Grower+Processor</option>
			<option>Processor</option>
			<option>Carrier</option>
			<option>Laboratory</option>
			<option>Retail</option>
		</select>
	</div>

	<div class="mt-4">
		<label>License Address:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="license-code"
			name="license-code"
			placeholder="Full License Address"
			tabindex="1"
			type="text"
			value=""
		>
	</div>

	<div class="mt-4">
		<label>License Phone:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="license-code"
			name="license-code"
			placeholder="License Phone"
			tabindex="1"
			type="text"
			value="<?= __h($data['license']['email']) ?>"
		>
	</div>

	<div class="mt-4">
		<label>License Email:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="license-code"
			name="license-code"
			placeholder="License Email"
			tabindex="1"
			type="text"
			value="<?= __h($data['company']['email']) ?>"
		>
	</div>

	</div>

	<div class="card-footer">
		<button class="btn btn-primary" name="a" tabindex="1" type="submit" value="license-request">Save <i class="icon icon-arrow-right"></i></button>
		<button class="btn btn-outline-secondary" id="btn-license-skip" name="a" tabindex="2" type="submit" value="license-skip">Skip <i class="icon icon-arrow-right"></i></button>

	</div>

	</div>

</div>
</form>


<script>
(function() {

	var B = document.querySelector('#btn-license-skip');
	B.addEventListener('click', function() {
		var node_list = document.querySelectorAll('input[required]');
		node_list.forEach(function(n) {
			n.removeAttribute('required');
		});
	});

})();
</script>
