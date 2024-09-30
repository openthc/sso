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
	<?php
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
