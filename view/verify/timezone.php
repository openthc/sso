<?php
/**
 * Select Timezone
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
		<label>Time Zone:</labeL>
		<div class="input-group">
			<select class="form-control" id="contact-timezone" name="contact-timezone">
			<?php
				foreach ($data['time_zone_list'] as $tz) {
					$sel = ($tz == $data['time_zone_pick'] ? ' selected' : '');
					$tz_nice = $tz;
					$tz_nice = preg_replace('/^\w+\//', '', $tz_nice); // Strip First Region
					$tz_nice = str_replace('/', ' / ', $tz_nice); // Space Remaining '/'
					$tz_nice = str_replace('_', ' ', $tz_nice); // Add Spaces
					printf('<option%s value="%s">%s</option>', $sel, $tz, $tz_nice);
				}
			?>
			</select>
			<button class="btn btn-outline-secondary"
				id="btn-timezone-pick"
				title="Detect Timezone from Browser"
				type="button">
				Detect <i class="fa-solid fa-wand-magic-sparkles"></i>
			</button>
		</div>
	</div>

</div>
<div class="card-footer">
	<button class="btn btn-primary" id="btn-timezone-save" name="a" type="submit" value="timezone-save-next">
		Next <?= Icon::icon('next') ?>
	</button>
</div>
</div>

</div>
</form>

<script>

function _timezone_pick()
{
	var tz0 = Intl.DateTimeFormat().resolvedOptions().timeZone;
	var opt = document.createElement('option');
	opt.value = tz0;
	opt.innerText = tz0;
	var sel = document.querySelector('#contact-timezone');
	sel.append(opt);
	sel.value = tz0;
}

var btn = document.querySelector('#btn-timezone-pick');
btn.addEventListener('click', _timezone_pick);

_timezone_pick();

</script>
