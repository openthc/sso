
<form autocomplete="off" method="post">
<div class="auth-wrap">

	<div class="card">
	<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
	<div class="card-body">

	<div class="form-group">
		<label>Email</label>
		<input autocomplete="off" class="form-control" id="contact-email" inputmode="email" name="contact-email" placeholder="eg: you@example.com" required type="email" value="">
	</div>

	</div>

	<div class="card-footer r">
		<button class="btn btn-primary" name="a" type="submit" value="contact-next">Create Account <i class="icon icon-arrow-right"></i></button>
	</div>

	</div>

</div>
</form>
