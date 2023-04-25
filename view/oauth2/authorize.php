<?php
/**
 * SPDX-License-Identifier: MIT
 */

?>

<div class="auth-wrap">
<div class="card">

	<h1 class="card-header">Application Authorization</h1>
	<div class="card-body">

		<h2 style="margin:0;">Account:</h2>
		<div class="form-group">
			<div class="form-control"><code><?= h($data['Contact']['username']) ?></code></div>
		</div>

		<h2 style="margin:0;">Company:</h2>
		<div class="form-group">
			<div class="input-group">
				<div class="form-control"><code><?= h($data['Company']['name']) ?></code></div>
				<a class="btn btn-outline-secondary" href="/auth/open" title="Switch to another organization"><i class="fas fa-building"></i> Switch</a>
			</div>
		</div>

		<h2 style="margin:0;">Service:</h2>
		<div class="form-group">
			<div class="form-control"><code><?= h($data['Service']['name']) ?></code></div>
		</div>

		<p>The service will be able to see data in scope: <code><?= h(implode(', ', $data['scope_list'])) ?></code>.</p>

	</div>
	<div class="card-footer">
		<div class="row">
		<div class="col">
			<div class="btn-group">
				<a
					class="btn btn-primary"
					id="oauth2-authorize-permit"
					href="/oauth2/permit?_=<?= $data['link_crypt'] ?>"
					style="width:8em;"
					title="Yes for this Session"
					>Yes</a>
				<a
					class="btn btn-outline-secondary"
					href="/oauth2/permit?_=<?= $data['link_crypt_save'] ?>"
					title="Yes for all Sessions"
					><i class="fas fa-check-square-o"></i> Yes &amp; Remember</a>
			</div>
		</div>

		<div class="col r">
			<a
				class="btn btn-outline-danger"
				id="oauth2-authorize-reject"
				href="/oauth2/reject?_=<?= $data['link_crypt'] ?>"
				style="width: 8em;"
				title="Do not Sign-In with this Account"
				>No</a>
		</div>
		</div>
	</div>

</div>
</div>
