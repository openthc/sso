<?php
/**
 *
 */

?>

<div class="container mt-2">

<div class="card">
<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
<div class="card-body">

	<form method="post">
	<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

	<div class="form-group">
		<label>Name</label>
		<div class="input-group">
			<input class="form-control" name="contact-name" type="text" value="<?= h($data['Contact_Base']['name']) ?>">
			<div class="input-group-append">
				<button class="btn btn-outline-primary" name="a" value="contact-name-save"><i class="fas fa-save"></i> Save</button>
			</div>
		</div>
	</div>
	</form>

	<div class="form-group">
		<label>Email / Username</label>
		<div class="input-group">
			<input class="form-control" name="contact-email" readonly type="email" value="<?= h($data['Contact_Auth']['username']) ?>">
			<div class="input-group-append">
				<button class="btn btn-outline-secondary" name="a" value="contact-email-update"><i class="fas fa-save"></i> Change</button>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label>Phone</label>
		<div class="input-group">
			<input class="form-control" name="contact-phone" type="tel" value="<?= h($data['Contact_Base']['phone']) ?>">
			<div class="input-group-append">
				<button class="btn btn-outline-secondary" name="a" value="contact-phone-update">Change</button>
			</div>
		</div>
	</div>

</div>
</div>


<?php
if ($data['company_list']) {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Company Connections</h2>
		<div class="card-body">
			<?php
			foreach ($data['company_list'] as $c) {
			?>
				<h3><a href="https://directory.openthc.com/company/<?= $c['id'] ?>" target="_blank"><?= h($c['name']) ?></a></h3>
			<?php
			}
			?>
		</div>
		<div class="card-footer">
			<a class="btn btn-outline-secondary" href="/company/join">Join Another Company</a>
		</div>
	</div>
<?php
}
?>

<?php
if ($data['service_list']) {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Service Connections</h2>
		<div class="card-body">
			<?php
			foreach ($data['service_list'] as $s) {
			?>
				<h3><a href="<?= $s['link'] ?>" target="_blank"><?= h($s['name']) ?></a></h3>
			<?php
			}
			?>
		</div>
	</div>
<?php
} else {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Service Connections</h2>
		<div class="card-body">
			<p>You do not appear to have any service connections</p>
			<p>You will want to connect your account to one, or more services to take full advantage of the OpenTHC Plantform</p>

			<a class="btn btn-lg btn-outline-primary" href="https://dir.openthc.dev/auth/open?v=sso">Connect DIR</a>
			<a class="btn btn-lg btn-outline-primary" href="https://app.openthc.dev/auth/open?v=sso">Connect AMP</a>
			<a class="btn btn-lg btn-outline-primary" href="https://pos.openthc.dev/auth/open?v=sso">Connect POS</a>

		</div>
	</div>

<?php
}
?>

</div>
