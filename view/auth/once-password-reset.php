
<form method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="auth-wrap">
<div class="card">

<h1 class="card-header"><?= $data['Page']['title'] ?></h1>

<div class="card-body">

	<?= $data['auth_hint'] ?>

	<div class="form-group">
		<label>Email</label>
		<input class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" value="<?= h($data['auth_username']) ?>">
	</div>

	<div class="form-group">
		<div class="g-recaptcha" data-sitekey="<?= $data['Google']['recaptcha_public'] ?>"></div>
	</div>

</div>

<div class="card-footer">
	<div class="row">
		<div class="col-md-6">
			<button class="btn btn-success" id="btn-password-reset" name="a" type="submit" value="password-reset-request">Request Password Reset</button>
		</div>
	</div>
</div>

</div>
</div>
</form>

<script src="https://www.google.com/recaptcha/api.js"></script>
