
<form autocomplete="off" method="post">
<div class="auth-wrap">

<div class="card">
<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
<div class="card-body">

	<div class="form-group">
		<label>Time Zone:</labeL>
		<div class="input-group">
			<select class="form-control" id="contact-timezone" name="contact-timezone">
			<?php
				foreach ($data['time_zone_list'] as $tz) {
					$sel = ($tz == $data['time_zone_pick'] ? ' selected' : '');
					$tz_nice = preg_replace('/^\w+\//', '', $tz);
					$tz_nice = str_replace('/', ' / ', $tz_nice);
					$tz_nice = str_replace('_', ' ', $tz_nice);
					printf('<option%s value="%s">%s</option>', $sel, $tz, $tz_nice);
				}
			?>
			</select>
			<div class="input-group-append">
				<button class="btn btn-outline-secondary"
					id="btn-timezone-pick"
					title="Detect Timezone from Browser"
					type="button">
					<i class="icon icon-magic-wand"></i> Detect
				</button>
			</div>
		</div>
	</div>

</div>
<div class="card-footer r">
	<button class="btn btn-primary" name="a" type="submit" value="timezone-save-next">
		Next
		<i class="icon icon-arrow-right"></i>
	</button>
</div>
</div>

</div>
</form>

<script>
function _time_zone_pick()
{
	var btn = document.querySelector('#btn-timezone-pick');
	btn.addEventListener('click', function(e) {
		var txt = document.querySelector('#contact-timezone');
		var tz0 = Intl.DateTimeFormat().resolvedOptions().timeZone;
		txt.value = tz0;
	});

}
_time_zone_pick();
</script>
