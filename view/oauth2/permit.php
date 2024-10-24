<?php
/**
 * SPDX-License-Identifier: MIT
 */

?>

<div class="auth-wrap">
<div class="card">

	<h1 class="card-header">Application Permitted</h1>
	<div class="card-body">

		<div class="mb-4">
			<h2>Account:</h2>
			<div class="form-control"><code><?= h($data['Contact']['username']) ?></code></div>
		</div>

		<div class="mb-4">
			<h2>Company:</h2>
			<div class="form-control"><code><?= h($data['Company']['name']) ?></code></div>
		</div>

		<div class="mb-4">
			<h2>Service:</h2>
			<div class="form-control"><code><?= h($data['Service']['name']) ?></code></div>
		</div>

		<p>You have selected to <strong>PERMIT</strong> access to this application.</p>

	</div>
	<div class="card-footer">
		<a class="btn btn-primary" id="oauth2-permit-continue" href="<?= $data['return_url'] ?>">Continue <i class="fas fa-arrow-right"></i></a>
	</div>

</div>
</div>
