
<form autocomplete="off" method="post">
<div class="auth-wrap">

	<div class="card">
	<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
	<div class="card-body">

		<div class="form-group">
			<label>Country:</label>
			<input class="form-control" disabled type="text" value="<?= h($_SESSION['iso3166_1']['official_name']) ?>">
		</div>

		<div class="form-group">
			<label>Region:</label>
			<select class="form-control" name="contact-iso3166-2">
			<?php
			$pick = sprintf('%s-%s', $_SESSION['iso3166_1']['alpha_2'], $data['iso3166_2_pick']['code']);
			foreach ($data['iso3166_2_list'] as $i => $x) {
				$sel = ($x['code'] == $pick ? ' selected' : '');
				printf('<option%s value="%s">%s</option>', $sel, $x['code'], $x['name']);
			}
			?>
			</select>
		</div>

	</div>
	<div class="card-footer r">
		<button class="btn btn-primary" name="a" type="submit" value="iso3166-2-save-next">
			Next
			<i class="icon icon-arrow-right"></i>
		</button>
	</div>
	</div>
</div>
</form>
