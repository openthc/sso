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
	foreach ($data['company_list'] as $Company) {

		if ($Company['id'] == $_SESSION['Company']['id']) {
			$Company['profile_select'] = true;
		}

		$Company['link'] = sprintf('%s/company/%s', $dir_origin, $Company['id']);
		$link_verify = sprintf('%s/company/%s/verify', $dir_origin, $Company['id']);
		// <!-- <p>Verify? <a href="$link_verify">Verify Company on DIR</a></p> -->
	?>
		<div class="d-flex justify-content-between">
			<div>
				<h3>
					<a href="<?= $Company['link'] ?>" target="_blank"><?= __h($Company['main_name']) ?></a>
					<?php
					if ($Company['profile_select']) {
						echo '<i class="fa-solid fa-star-of-life"></i>';
					}
					?>
				</h3>
			</div>
			<div>
			<?php
			if (empty($Company['guid'])) {
				echo 'Needs ID';
			} else {
				printf('<code>%s</code>', __h($Company['guid']));
			}
			?>
			</div>
			<?php
			// Necessary Datas
			if ( ! empty($Company['iso3166'])) {
				echo '<div><i class="fa-solid fa-map-location-dot text-success"></i></div>';
			} else {
				echo '<div><i class="fa-solid fa-map-location-dot text-danger"></i></div>';
			}

			if ( ! empty($Company['tz'])) {
				echo '<div><i class="fa-regular fa-clock text-success"></i></div>';
			} else {
				echo '<div><i class="fa-regular fa-clock text-danger"></i></div>';
			}

			echo '<div>';
			switch ($Company['main_stat']) {
			case 100:
				printf('<a href="%s" target="_blank" title="Verify Company">New</a>', $link_verify);
				break;
			case 200:
				printf('<a href="%s" target="_blank" title="Verify Company">Active/Unverified</a>', $link_verify);
				break;
			case 202:
				printf('<a href="%s" target="_blank" title="Verify Company">Active/Verified</a>', $link_verify);
				break;
			case 410:
				echo '<span class="text-danger">Closed</span>';
				break;
			default:
				echo $Company['main_stat'];
			}
			echo '</div>';

			echo '<div>';
			switch ($Company['auth_stat']) {
			case 100:
				echo 'Auth:Pending';
				break;
			case 200:
				echo 'Auth:Active';
				break;
			case 402:
				echo 'Auth:Payment';
				break;
			}
			echo '</div>';

			// Link to app/onboard/cre to configure this
			// Needs to pass the selected Company context too
			if ( ! empty($Company['cre'])) {
				echo '<div><i class="fa-solid fa-cloud-arrow-up text-success"></i></div>';
			} else {
				echo '<div><i class="fa-solid fa-cloud-arrow-down text-danger"></i></div>';
			}

			if ( ! empty($Company['dsn'])) {
				echo '<div><i class="fa-solid fa-database text-primary" title="Have Database"></i></div>';
				// 	echo '<div class="alert alert-warning">This Company Profile is not active yet</div>';
			}

			?>
		</div>
		<!-- <div><pre><?php var_dump($Company); ?></pre> -->
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
