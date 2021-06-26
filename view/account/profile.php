
<div class="auth-wrap">

<form method="post">
<div class="card">
<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
<div class="card-body">

	<div class="form-group">
		<label>Name</label>
		<input class="form-control" name="contact-name" type="text" value="<?= h($data['Contact_Base']['name']) ?>">
	</div>

	<div class="form-group">
		<label>Email / Username</label>
		<input class="form-control" name="contact-email" readonly type="email" value="<?= h($data['Contact_Auth']['username']) ?>">
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
<div class="card-footer">
	<button class="btn btn-outline-primary"><i class="fas fa-save"></i> Save</button>
</div>
</div>
</form>

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
				<h3><?= h($cp['name']) ?></h3>
			<?php
			}
			?>
		</div>
	</div>
<?php
}
?>

<?php
if ($data['service_list') {
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
}
?>

</div>
