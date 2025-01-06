<?php
/**
 * Show Company List
 *
 * SPDX-License-Identifier: MIT
 */

?>

<hr>

<div class="card">

<h2 class="card-header">Company Connections</h2>

<div class="card-body">
<?php

if (empty($data['company_list'])) {
?>
	<div class="alert alert-warning">You are not connected to any Company profiles</div>
<?php
} else {
	foreach ($data['company_list'] as $c) {
	?>
		<h3><a href="<?= sprintf('%s/company/%s', $dir_origin, $c['id']) ?>" target="_blank"><?= h($c['name']) ?></a></h3>
		<p>Verify? <a href="https://<?= $dir_origin ?>/company/<?= $c['id'] ?>/verify">Verify Company on DIR</a></p>
	<?php
		if (empty($c['dsn'])) {
			echo '<div class="alert alert-warning">This Company Profile is not active yet</div>';
		}
	}
}
?>
</div>

<!--
<div class="card-footer">
	<a class="btn btn-primary" href="/company/join">Join Another Company</a>
</div>
-->

</div>
