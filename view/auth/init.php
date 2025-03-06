<?php
/**
 * SPDX-License-Identifier: MIT
 */

?>

<form method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="container mt-4">
<h1>Select Company</h1>

<div class="row">
<?php
foreach ($data['company_list'] as $Company) {

	$css0 = 'btn-outline-secondary';
	if ($Company['flag_default']) {
		$css0 = 'btn-primary';
	}

?>
	<div class="col-md-4 mb-4">

		<div class="card" style="height: 100%;">
			<div class="card-body">
				<h2><?= h($Company['name']) ?></h2>
			</div>
			<div class="card-footer">
				<button class="btn <?= $css0 ?>" id="btn-company-<?= $Company['id'] ?>" type="submit" name="company_id" value="<?= $Company['id'] ?>">Open Company Account</button>
			</div>
		</div>

	</div>
<?php
}
?>
</div>

</div>
</form>
