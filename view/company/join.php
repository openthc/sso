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

		<input id="company-id" name="company-id" type="hidden" value="">
	</div>

	<div class="mt-4">
		<label>Region:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="company-region"
			name="company-region"
			placeholder="Region"
			tabindex="1"
			type="text"
			value="<?= __h($_SESSION['Contact']['iso3166']) ?>">
	</div>

	<div class="mt-4">
		<label>Government ID:</label>
		<input
			autocomplete="off"
			class="form-control"
			id="company-guid"
			name="company-guid"
			placeholder="Company Government ID"
			tabindex="1"
			type="text"
			value="">
	</div>

</div>

<div class="card-footer">
	<div class="d-flex justify-content-between">
		<div>
			<button class="btn btn-primary" name="a" tabindex="1" type="submit" value="company-join">
				Save <?= Icon::icon('save') ?>
			</button>
		</div>
		<!-- <div>
			<button class="btn btn-outline-secondary" id="btn-company-skip" name="a" tabindex="2" type="submit" value="company-skip">
				Skip <?= Icon::icon('checkbox') ?>
			</button>
		</div> -->
	</div>
</div>

</div>
</div>
</form>


<script>
(function() {

	// var B = document.querySelector('#btn-company-skip');
	// B.addEventListener('click', function() {
	// 	var node_list = document.querySelectorAll('input[required]');
	// 	node_list.forEach(function(n) {
	// 		n.removeAttribute('required');
	// 	});
	// });

	<?php
	$dir_origin = \OpenTHC\Config::get('openthc/dir/origin');
	if ( ! empty($dir_origin)) {
	?>
		$('#company-name').autocomplete({
			source: '<?= $dir_origin ?>/api/autocomplete/company',
			select: function(e, ui) {
				$('#company-id').val( ui.item.company.id);
				$('#company-guid').val( ui.item.company.guid);
				$('#company-name').val( ui.item.company.name );
				$('#company-region').val( ui.item.company.region);
				return false;
			}
		});
	<?php
	}
	?>
})();
</script>
