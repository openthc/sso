
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

	<div class="form-group">
		<label>Email</label>
		<input autofocus class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" type="email" value="<?= h($data['auth_username']) ?>">
	</div>

	<div class="form-group">
		<label>Password</label>
		<input class="form-control" id="password" name="password" type="password" value="<?= h($data['auth_password']) ?>">
	</div>

	</div>

	<div class="card-footer">
		<div class="row no-gutters">
			<div class="col-md-6">
				<button class="btn btn-primary" id="btn-auth-open" name="a" type="submit" value="account-open">Sign In</button>
			</div>
			<div class="col-md-6 r">
				<a class="btn btn-outline-secondary" href="/auth/open?a=password-reset">Forgot Password</a>
				<a class="btn btn-outline-secondary" href="/account/create?service=<?= $data['service'] ?>">Create an Account</a>
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
