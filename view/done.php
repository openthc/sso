<?php
/**
 * End of the Line
 *
 * SPDX-License-Identifier: MIT
 */

if ( ! empty($data['error_code'])) {
	$data['Page']['title'] = sprintf('Error: %s', $data['error_code']);
	switch ($data['error_code']) {
		case 'CVB-030':
			$data['fail'] = 'Invalid Request';
			break;
		case 'CAO-040':
			$data['fail'] = 'Invalid Request, Token Expired or Invalid';
			break;
		default:
			$data['Page']['title'] = sprintf('Error: %s', $data['error_code']);
	}
}

?>

<div class="auth-wrap">

	<div class="card">
	<h1 class="card-header"><?= $data['Page']['title'] ?></h1>
	<div class="card-body">

		<?php
		if ($data['fail']) {
			printf('<div class="alert alert-danger">%s</div>', h($data['fail']));
		}

		if ($data['warn']) {
			printf('<div class="alert alert-warning">%s</div>', h($data['warn']));
		}

		if ($data['info']) {
			printf('<div class="alert alert-info">%s</div>', h($data['info']));
		}

		echo $data['body'];

		// It's the Secret Token
		if ( ! empty($_GET['t'])) {
			$sso_origin = OPENTHC_SERVICE_ORIGIN;
			echo sprintf('<hr><div class="alert alert-warning">Auth: <a href="%s/auth/once?_=%s">SSO/auth/once</a></div>', $sso_origin, $_GET['t']);
		}


		?>

	</div>
	<?php
	if ($data['foot']) {
		printf('<div class="card-footer">%s</div>', $data['foot']);
	}
	?>
	</div>
</div>
