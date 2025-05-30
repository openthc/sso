<?php
/**
 * Show Company List
 *
 * SPDX-License-Identifier: MIT
 */

$dir_origin = \OpenTHC\Config::get('openthc/dir/origin');

$company_search_link = sprintf('%s/auth/open?%s', $dir_origin, http_build_query([
	'r' => '/search'
]));

?>

<hr>

<div class="card">

<h2 class="card-header">Company Connections</h2>

<div class="card-body">
<?php
if (empty($data['company_list'])) {
?>
	<div class="alert alert-warning">You are not connected to any Company profiles</div>
	<div class="container">
		<div class="col-md-8 mx-auto">
			<p>In the OpenTHC Universe some actions, such as App and POS require an active Company profile.</p>
			<p>The Company profile is the container for one or more License configurations.</p>
			<p>You can <a href="<?= $company_search_link ?>" target="_blank">search the Directory</a> for your Company and request to join.  If you don't have a company profile you should create one.</p>
			<p>If the Company profile is unclaimed the process to verify is simple.</p>
			<p>If the Company profile is already claimed, they should perhaps invite you or we would need to begin a <em>re-claim</em> process.</p>
		</div>
	</div>
<?php
} else {

	echo '<table class="table">';

	foreach ($data['company_list'] as $Company) {

		if ($Company['id'] == $_SESSION['Company']['id']) {
			$Company['profile_select'] = true;
		}

		$Company['link'] = sprintf('%s/company/%s', $dir_origin, $Company['id']);
		$link_verify = sprintf('%s/company/%s/verify', $dir_origin, $Company['id']);
		// <!-- <p>Verify? <a href="$link_verify">Verify Company on DIR</a></p> -->
	?>
		<tr>
			<td>
				<h3>
					<a href="<?= $Company['link'] ?>" target="_blank"><?= __h($Company['main_name']) ?></a>
					<?php
					if ($Company['profile_select']) {
						echo '<i class="fa-solid fa-star-of-life"></i>';
					}
					?>
				</h3>
			</td>
			<td>
			<?php
			if (empty($Company['guid'])) {
				echo 'Needs ID';
			} else {
				printf('<code>%s</code>', __h($Company['guid']));
			}
			?>
			</td>

			<td>
			<?php
			// Necessary Data
			if ( ! empty($Company['iso3166'])) {
				echo '<i class="fa-solid fa-map-location-dot text-success"></i>';
			} else {
				echo '<i class="fa-solid fa-map-location-dot text-danger"></i>';
			}
			?>
			</td>
			<td>
			<?php
			if ( ! empty($Company['tz'])) {
				echo '<i class="fa-regular fa-clock text-success"></i>';
			} else {
				echo '<div title="Needs Timezone Configured"><i class="fa-regular fa-clock text-danger"></i></div>';
			}
			?>
			</td>

			<?php
			echo '<td>';
			switch ($Company['main_stat']) {
			case 100:
				printf('<a href="%s" target="_blank" title="Verify Company">New</a>', $link_verify);
				break;
			case 102:
				printf('<a href="%s" target="_blank" title="Verify Company">Pending</a>', $link_verify);
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
				printf('<code>%s</code>', $Company['main_stat']);
			}
			echo '</td>';

			echo '<td>';
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
			default:
				printf('<code>%s</code>', $Company['auth_stat']);
			}
			echo '</td>';

			// Link to app/onboard/cre to configure this
			// Needs to pass the selected Company context too
			echo '<td>';
			if ( ! empty($Company['cre'])) {
				echo '<div><i class="fa-solid fa-cloud-arrow-up text-success"></i></div>';
			} else {
				echo '<div><i class="fa-solid fa-cloud-arrow-down text-danger"></i></div>';
			}
			echo '</td>';

			echo '<td>';
			if ( ! empty($Company['dsn'])) {
				echo '<div><i class="fa-solid fa-database text-primary" title="Have Database"></i></div>';
				// 	echo '<div class="alert alert-warning">This Company Profile is not active yet</div>';
			}
			echo '</td>';
			?>
		</tr>
		<!-- <div><pre><?php var_dump($Company); ?></pre> -->
	<?php
	}
	echo '</table>';
}
?>
</div>
<div class="card-footer">
<?php
if ( ! empty($data['company_list'])) {
?>
	<!-- <a class="btn btn-primary" href="/auth/open?a=switch-company">Switch Active Company</a> -->
<?php
}
?>
	<a class="btn btn-secondary" href="/company/join">
		<i class="fa-solid fa-building-user"></i> Join Company
	</a>
</div>

</div>
