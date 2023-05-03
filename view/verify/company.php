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

	<div style="font-size: 120%;">
		<p>All accounts in the OpenTHC system are required to be linked to a company.</p>
		<p>If you do not have a company, like an individual operator, or a CO-OP you may skip this step</p>
	</div>

	<div class="form-group">
		<label>Company</label>
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

	<div class="form-group">
		<label>Address:</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="company-address"
			name="company-address"
			placeholder="Company Full Address"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

	<div class="form-group">
		<label>Phone:</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="company-phone"
			name="company-phone"
			placeholder="Company Phone"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

	<div class="form-group">
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
			value="">
	</div>

	<div class="form-group">
		<label>Government ID:</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="company-email"
			name="company-email"
			placeholder="Company Government ID"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

</div>

<div class="card-footer">
	<button class="btn btn-primary" name="a" tabindex="1" type="submit" value="company-request">Save <i class="icon icon-arrow-right"></i></button>
	<button class="btn btn-outline-secondary" id="btn-company-skip" name="a" tabindex="2" type="submit" value="company-skip">Skip <i class="icon icon-arrow-right"></i></button>
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

})();
</script>
