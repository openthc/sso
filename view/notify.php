<?php
/**
 * Render the user notification
 *
 * SPDX-License-Identifier: MIT
 */


?>

<form method="post">
<div class="container mb-4" style="padding-top: 50px;">
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card">
	<div class="card-header"><h2><?= h($data['head']) ?></h2></div>`
	<div class="card-body"><?= _markdown($data['body']) ?></div>
	<div class="card-footer">
		<input name="notify_id" type="hidden" value="<?= $data['id'] ?>">
		<input name="next_url" type="hidden" value="<?= $data['next_url'] ?>">
		<button type="submit" class="btn btn-outline-primary"
			data-track data-track-c="app-alert"
			data-track-a="clicked-OK" class="btn btn-outline-primary">OK</button>
	</div>
</div>
</div>
</div>
</div>
</form>
