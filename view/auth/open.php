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

<form method="post" id="auth-open-form">

<input id="js-enabled" name="js-enabled" type="hidden" value="0">
<input id="date-input-enabled" name="date-input-enabled" type="hidden" value="0">
<input id="time-input-enabled" name="time-input-enabled" type="hidden" value="0">
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
		<input autofocus class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" required type="email" value="<?= __h($data['auth_username']) ?>">
	</div>

	<div class="mt-4">
		<label>Password</label>
		<input class="form-control" id="password" name="password" required type="password" value="<?= __h($data['auth_password']) ?>">
	</div>

	</div>

	<div class="card-footer">
		<div class="d-flex justify-content-between">
			<div>
				<button class="btn btn-primary" id="btn-auth-open" name="a" type="submit" value="account-open">
					Sign In <i class="fa-solid fa-arrow-right-to-bracket"></i>
				</button>
			</div>
			<div>
				<a class="btn btn-outline-secondary" href="<?= $link_pwd ?>">Forgot Password <i class="fa-regular fa-circle-question"></i></a>
				<a class="btn btn-outline-secondary" href="<?= $link_new ?>">Create Account <i class="fa-solid fa-user-plus"></i></a>
			</div>
		</div>
	</div>

	</div>

</div>
</form>


<script>
document.querySelector('#js-enabled').value = 1;
document.querySelector('#date-input-enabled').value = (function() {
	var node = document.createElement('input');
	node.type = 'date';
	return ('date' === node.type)
})();
document.querySelector('#time-input-enabled').value = (function() {
	var node = document.createElement('input');
	node.type = 'time';
	return ('time' === node.type)
})();
var F = document.querySelector('#auth-open-form');
if (F) {
	F.addEventListener('submit', function() {
		var wrap = F.querySelector('.auth-wrap');
		wrap.style.backgroudColor = '#11111111';
		wrap.style.pointerEvents = 'none';
		wrap.style.opacity = '0.5';
		var btn = F.querySelector('#btn-auth-open');
		btn.innerHTML = 'Here we go!';
	});
}
</script>
