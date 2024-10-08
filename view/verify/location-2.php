<?php
/**
 * Select the Second Level Location
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
			<label>Country:</label>
			<input class="form-control" disabled type="text" value="<?= h($_SESSION['iso3166_1_pick']['name']) ?>">
		</div>

		<div class="mt-4">
			<label>Region:</label>
			<select class="form-select" id="contact-iso3166-2" name="contact-iso3166-2">
			<?php
			foreach ($data['iso3166_2_list'] as $i => $x) {
				$sel = ($x['id'] == $data['iso3166_2_pick']['id'] ? ' selected' : '');
				printf('<option%s value="%s">%s</option>', $sel, $x['id'], $x['name']);
			}
			?>
			</select>
		</div>

	</div>
	<div class="card-footer">
		<button class="btn btn-primary" id="btn-location-save" name="a" type="submit" value="iso3166-2-save-next">
			Next <?= Icon::icon('next') ?>
		</button>
	</div>
	</div>
</div>
</form>
