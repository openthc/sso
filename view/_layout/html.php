<?php
/**
 * SPDX-License-Identifier: MIT
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="application-name" content="OpenTHC">
<link href="/vendor/bootstrap/bootstrap.min.css" integrity="sha256-MBffSnbbXwHCuZtgPYiwMQbfE7z+GOZ7fBPCNB06Z98=" crossorigin="anonymous" rel="stylesheet">
<link href="/vendor/fontawesome/css/all.min.css" integrity="sha256-CTSx/A06dm1B063156EVh15m6Y67pAjZZaQc89LLSrU='" crossorigin="anonymous" rel="stylesheet">
<style>
body {
	display: flex;
	flex-direction: column;
	height: 100vh;
	width: 100%;
}
main {
	flex: 1 1 100%;
}
footer {
	background: #343a40;
	border: 1px solid #343a40;
	color: #ffffffaa;
	display: flex;
	flex-direction: row;
	flex-wrap: nowrap;
	justify-content: space-between;
	margin: 5vh 0 0 0;
	padding: 1vh 2vw 2vh 2vw;
}
footer a {
	color: #ffffffaa;
}

.auth-wrap {
	margin: 2vh auto 0 auto;
	max-width: 690px;
}

.r {
	text-align: right;
}

#alert-test-mode {
	bottom: 5vh;
	position: fixed;
	left: 5vw;
	right: 5vw;
	z-index: 16384;
}

#alert-test-mode .alert {
	border-radius: 0;
	margin: 0;
	text-align: center;
}
</style>
<title><?= $data['Page']['title'] ?: 'SSO' ?></title>
</head>
<body>

<nav class="navbar navbar-dark bg-dark sticky-top">
<div class="container-fluid">

	<a class="navbar-brand" href="/">
		<img alt="OpenTHC Icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXM
AAC4jAAAuIwF4pT92AAAE4UlEQVRYw8WXW2xUVRSGv7Vn2gKxCF6ipAW0mHgBbVR6TiECUuSQGInhYjNAEAqiQUnQ8OADzxLjE30hRhFa0wqhECOiiYMBNQVnpkBsECHGUAgMhdICFUrb6Zmzf
OgM6WU6M5QS99O57L3+f63177XXFoY5wpHgw8BioEOEH60S58Zw7JhsJx4/cSgJTDgSrAZOAl8Atar8GY4Et/UhlzUByeAltuX0PoeDhQgrgE8z2NwEfGNbzuV7JpAg8QBQB5QC47J0rA34HVh
kW46bNYGGhoOUlMxPAj8LvA9sSLNes3BiiyrVpbbzN0Ao/BOl9oKhIxBpODhNVbcBJcCoNIZPAOuBHOAr4Ok0czuBkKq+U2ovOJsyAuFI8HFgF/BqGkM9wHGBTZblHB2QKgf4BHgR8KWx8T2w2racawN3wSsZwKuBl23LmWFZztFIH6UnxBpMRG068F0aOwuBqakisBjYN2DyLSAELLct5+pd1YmGgxNRrQZsYMyA37Nsy6nPVAcuArZtOfNty7l6N3s7fOxn7JL5F2zLKQPmAK3DKURdBnMu+ZKsB9kMe/prfV/PJ7Rzb5Xwfg3zP5GRVKBHEuG63+Mv4FQ/AqZ2DbblNNuW8wTwcUI0OSMImgtcBnnXtpyptuVcm3xgFgB+AG/FDkxNRbEf+ce2nM9CkWCVqs42MiLgcWCtovWlltP21JH5+fGYW9g09/Dp/rmoXbMU1TqUdX6P2tiqnZ0jGfcpv8zN93yyRoxsVdd7o2nO4R8GasBDBIx86frljKmpWAFgvl0/bNCCxsUAFNWXvae55rT4ZCsCKN5gEWq/tZMQqTE1FXu52TUl+dG/c1VG0Cd/nXvnOe/mjWeKjszbj5HPgYIBp+gAArdjKTaLLMEnZ0xNRR2AW1Hdu+jr1SnBJx6YSdOcwxTum5FbVF8WxMgphIWDRNHR03HHqTuU3LiKpzBYeX5ElpraNS6eLsKNH/TerupKRSAnP2/Mk/Vlr4uRuiEbCFfxetQbRIBu9zKj4pDnH2qtDyP7yfVHTM1aRzTeHl9Z1Zvj38oEo+MQc1iE4nQp8rpcvFtu9+AUGGnRfzs7stCWBd72+Moq/MHeZuns7EMKZi8ZwFUVtzXWJn5pTtkRSWX5HxgplvFjQDIUAc9b7q2s2pVQ+XqMbEtbeONK96VO8DQSDYTsoer/OjxFW26inW76jk/M5j5i3TAksKfE23voOncLPAVYkvIwkspydOOeBuBNfAY6utBrHWhnT+poCONNzepHio7OexRhfKpjzm2PEYvexr0eQ/wGYF40ELpYsLs0fVsuleUlwA5gGqqQ40fy88DXL2BXUIonFzblS45pRBJdj4DG4vS0dqMxL0n+JFAeDYTOZOwHkpHQjXueB2YicgM3jl6/jbZ3gmqqpl4QUE/pudxJrLkL7VEQuQC8FA2EXogGQmf6ep7xYiJb30I/rEuS2g4sAh7CU3hwFJLrv4IxxZMLzo7FJ41eR3y029aVrCOtQE00EPoIoGCXTXRZeHg3oz6EJiCyGfgABXxyBSPFBRPOj9Y4jbg6NlHnt6ho5aVAuGVEL6eINOvGPRuA5xAa8XQUrofXrR5x9SEcU5gUXRbaLF5uS9Zm76qP6tUHUlmem+j/T0547LxPkKlAKBoIxSfsLqU5EMra5n9rjuwHDvgo6gAAAABJRU5ErkJggg==">
	</a>

	<?php
	if ( ! empty($_SESSION['Contact']['id'])) {
		echo '<a class="btn btn-sm btn-outline-secondary" href="/auth/shut"><i class="fas fa-power-off"></i> Sign Out</a>';
	}
	?>

</div>
</nav>

<?php
if (!empty($data['Page']['flash'])) {
?>
	<div class="container mt-4">
		<?= $data['Page']['flash'] ?>
	</div>
<?php
}
?>

<main>
<?= $this->body ?>
</main>

<footer>
	<div><a href="https://openthc.com/">openthc.com</a></div>
	<div><a href="https://openthc.com/about/tos">Terms of Service</a></div>
	<div><a href="https://openthc.com/about/privacy">Privacy</a></div>
</footer>

<?php
if ('TEST' == getenv('OPENTHC_TEST')) {
?>
	<div id="alert-test-mode">
		<div class="alert alert-danger">TEST MODE</div>
	</div>
<?php
}
?>

<script src="/vendor/jquery/jquery.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="/vendor/bootstrap/bootstrap.bundle.min.js" integrity="sha256-gvZPYrsDwbwYJLD5yeBfcNujPhRoGOY831wwbIzz3t0=" crossorigin="anonymous"></script>
<?= $this->foot_script ?>
</body>
</html>
