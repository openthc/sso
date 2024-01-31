<?php
/**
 * Select the Top-Level Location
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
			<div class="input-group" id="country-select-wrap">
				<select class="form-control" id="contact-iso3166-1" name="contact-iso3166-1">
				<?php
				foreach ($data['iso3166_1_list'] as $i => $x) {
					$sel = ($x['id'] == $data['iso3166_1_pick']['id'] ? ' selected' : '');
					printf('<option%s value="%s">%s</option>', $sel, $x['id'], $x['name']);
				}
				?>
				</select>
			</div>
		</div>

	</div>
	<div class="card-footer">
		<button class="btn btn-primary" id="btn-location-save" name="a" type="submit" value="iso3166-1-save-next">
			Next <?= Icon::icon('next') ?>
		</button>
	</div>
	</div>
</div>
</form>

<script>
// Provides a Point
// Have to feedback to OpenCAGE or something
const successCallback = (pos) => {

	// console.log(pos);

	var fd0 = new FormData();
	fd0.set('a', 'geo-resolve');
	fd0.set('lat', pos.coords.latitude);
	fd0.set('lon', pos.coords.longitude);

	var arg = {
		'method': 'POST',
		'body': fd0
	};

	fetch('', arg)
		.then((res) => res.json())
		.then(function(res) {
			// console.log(res);
			if (res.data.iso3166_1) {
				var node = document.querySelector('#contact-iso3166-1');
				node.value = res.data.iso3166_1.id;
			}
		});

	$('#country-select-sync').remove();

};

const errorCallback = (err) => {
	console.log(err);
	$('#country-select-sync').remove();
};

$(function() {

	$('#country-select-wrap').append('<div class="input-group-text" id="country-select-sync"><i class="fa-solid fa-arrows-rotate"></i></div>');

	navigator.geolocation.getCurrentPosition(successCallback, errorCallback, {
		requestAddress: true
	});

});

</script>
