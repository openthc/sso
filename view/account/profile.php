<?php
/**
 * SPDX-License-Identifier: MIT
 */

$dir_origin = \OpenTHC\Config::get('openthc/dir/origin');

?>


<div class="container mt-2">

<div class="card">
<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
<div class="card-body">

	<form method="post">
	<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

	<div class="mt-4">
		<label>Name</label>
		<div class="input-group">
			<input class="form-control" name="contact-name" type="text" value="<?= __h($data['Contact_Base']['name']) ?>">
			<button class="btn btn-outline-primary" name="a" value="contact-name-save"><i class="fas fa-save"></i> Save</button>
		</div>
	</div>

	<div class="mt-4">
		<label>Password</label>
		<div class="input-group">
			<input class="form-control" readonly type="text" value="********">
			<button class="btn btn-outline-secondary" name="a" value="contact-password-update">Change</button>
		</div>
	</div>

	</form>

	<div class="mt-4">
		<label>Email / Username</label>
		<div class="input-group">
			<input class="form-control" name="contact-email" readonly type="email" value="<?= __h($data['Contact_Auth']['username']) ?>">
			<button class="btn btn-outline-secondary" name="a" value="contact-email-update"><i class="fas fa-save"></i> Change</button>
		</div>
	</div>

	<div class="mt-4">
		<label>Phone</label>
		<div class="input-group">
			<input class="form-control" name="contact-phone" readonly type="tel" value="<?= __h($data['Contact_Base']['phone']) ?>">
			<button class="btn btn-outline-secondary" name="a" value="contact-phone-update">Change</button>
		</div>
	</div>

</div>
</div>


<?php
// Show Service List
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
} elseif ($data['service_list_default']) {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Service Connections</h2>
		<div class="card-body">

			<p>You do not appear to have any service connections</p>
			<p>You will want to connect your account to one, or more services to take full advantage of the OpenTHC Platform</p>

			<?php
			foreach ($data['service_list_default'] as $s) {
			?>
				<div class="mb-4">
					<h3><?= __h($s['name']) ?></h3>
					<p><?= __h($s['hint']) ?></p>
					<a class="btn btn-lg btn-outline-primary btn-service-connect" data-service-name="<?= __h(strtolower($s['name'])) ?>" href="<?= $s['link'] ?>" target="_blank">Open <?= __h($s['name']) ?></a>
				</div>
			<?php
			}
			?>

		</div>
	</div>

<?php
}
?>


<?php
// Show Company List
if ($data['company_list']) {
?>
	<hr>
	<div class="card">
		<h2 class="card-header">Company Connections</h2>
		<div class="card-body">
			<?php
			foreach ($data['company_list'] as $c) {
			?>
				<h3><a href="<?= sprintf('%s/company/%s', $dir_origin, $c['id']) ?>" target="_blank"><?= h($c['name']) ?></a></h3>
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


</div>
