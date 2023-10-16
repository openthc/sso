<?php
/**
 * SPDX-License-Identifier: MIT
 */

$svc = $data['service'];

$link_pwd = '/auth/open?a=password-reset';
$link_new = '/account/create';
// <?= $data['service']->id
if ( ! empty($svc)) {
	$link_pwd = '/auth/open?' . http_build_query([
		'a' => 'password-reset',
		'service' => $svc->id,
	]);
	$link_new = '/account/create?' . http_build_query([
		'service' => $svc->id,
	]);
}

?>

<form method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="auth-wrap">

	<div class="card">
	<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
	<div class="card-body">

	<?= $data['auth_hint'] ?>

	<noscript>
		<div class="alert alert-danger">This web application <strong>requires</strong> JavaScript to be enabled.</div>
	</noscript>

	<div class="mt-4">
		<label>Email</label>
		<input autofocus class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" type="email" value="<?= h($data['auth_username']) ?>">
	</div>

	<div class="mt-4">
		<label>Password</label>
		<input class="form-control" id="password" name="password" type="password" value="<?= h($data['auth_password']) ?>">
	</div>

	</div>

	<div class="card-footer">
		<div class="d-flex justify-content-between">
			<div>
				<button class="btn btn-primary" id="btn-auth-open" name="a" type="submit" value="account-open">Sign In</button>
			</div>
			<div>
				<a class="btn btn-outline-secondary" href="<?= $link_pwd ?>">Forgot Password</a>
				<a class="btn btn-outline-secondary" href="<?= $link_new ?>">Create Account</a>
			</div>
		</div>
	</div>

	</div>

</div>
<div>
	<input id="js-enabled" name="js-enabled" type="hidden" value="0">
	<input id="date-input-enabled" name="date-input-enabled" type="hidden" value="0">
	<input id="time-input-enabled" name="time-input-enabled" type="hidden" value="0">
</div>
</form>


<script src="https://cdn.openthc.com/modernizr/2.8.3/modernizr.js" integrity="sha256-0rguYS0qgS6L4qVzANq4kjxPLtvnp5nn2nB5G1lWRv4=" crossorigin="anonymous"></script>
<script>
document.querySelector('#js-enabled').value = 1;
document.querySelector('#date-input-enabled').value = (Modernizr.inputtypes.date + 0);
document.querySelector('#time-input-enabled').value = (Modernizr.inputtypes.time + 0);
</script>
