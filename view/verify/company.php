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
	<!--
		<p>All accounts in the OpenTHC system are required to be linked to a company.</p>
		<p>If you do not have a company, like an individual operator, or a CO-OP you may skip this step</p>
	-->
		<p>All accounts in OpenTHC must be linked to a regulatory license.</p>
		<p>If your region or industry sector does not issue a separate license from a typical business license, you may leave this blank.</p>
	</div>

	<div class="form-group">
		<label>Company</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="company-name"
			name="company-name"
			placeholder="Your Company Name"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

	<div class="row">
	<div class="col-md-6">
	<div class="form-group">
		<label>License:</label>
		<input
			autocomplete="off"
			autofocus
			class="form-control"
			id="license-code"
			name="license-code"
			placeholder="Your License Number"
			tabindex="1"
			type="text"
			value=""
		>
	</div>
	</div>

	<div class="col-md-6">
		<div class="form-group">
			<label>License Type:</label>
			<select
				autocomplete="off"
				autofocus
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
	</div>
	</div> <!-- ./row -->

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
		var T = document.querySelector('#company-name');
		T.removeAttribute('required');
	});

})();
</script>
