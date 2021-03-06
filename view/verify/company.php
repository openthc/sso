
<form autocomplete="off" method="post">
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
			placeholder="Your Company Name"
			required
			tabindex="1"
			type="text"
			value="">
	</div>

</div>

<div class="card-footer r">
	<button class="btn btn-outline-secondary" id="btn-company-skip" name="a" tabindex="2" type="submit" value="company-skip">Skip <i class="icon icon-arrow-right"></i></button>
	<button class="btn btn-primary" name="a" tabindex="1" type="submit" value="company-save">Save <i class="icon icon-arrow-right"></i></button>
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
