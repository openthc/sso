<?php
/**
 * OpenTHC SSO Bootstrap
 */

define('APP_ROOT', __DIR__);
define('APP_BUILD', '420.20.040');

error_reporting(E_ALL & ~ E_NOTICE);

openlog('openthc-sso', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');

function _exit_html_err($err, $code=500)
{
	if (is_string($err)) {
		$err = [
			'code' => 'Error',
			'head' => 'System Error',
			'body' => $err,
		];
	}

	// Add a Link to the Error Code
	if (preg_match('/\[(\w{3}(#|\-)\w{3})\]/', $err['body'], $m)) {

		$rem = $m[0];

		$tag = $m[1];
		$tag = str_replace('#', '-', $tag); // remove after all tags are updated

		$rep = sprintf('[<a href="https://openthc.com/err#%s" target="_blank">%s</a>]', $tag, $tag);

		$err['body'] = str_replace($rem, $rep, $err['body']);
	}

	$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#111111">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.openthc.com/bootstrap/4.4.1/bootstrap.css" integrity="sha256-L/W5Wfqfa0sdBNIKN9cG6QA5F2qx4qICmU2VgLruv9Y=" crossorigin="anonymous">
<title>OpenTHC :: {$err['head']}</title>
<style>
body {
	background: #111111;
	display: flex;
	flex-direction: column;
	font-family: sans-serif;
	font-size: 100%;
	height: 100vh;
	margin: 0;
	padding: 0;
	width: 100vw;
}
main {
	flex: 1 1 100%;
}
main .container {
	margin: 10vh auto 0 auto;
	max-width: 800px;
}
footer {
	align-content: space-between;
	align-items: center;
	background: #343a40;
	border-top: 1px solid #343a40;
	display: flex;
	flex-direction: row;
	flex: 0 1 auto;
	justify-content: space-around;
	margin: 0;
	padding: 0.50rem;
}
footer a {
	color: #f9f9f9;
	text-decoration: none;
}
</style>
</head>
<body>
<main>

<div class="container">
<div class="card">

	<h1 class="card-header">{$err['head']}</h1>

	<article class="card-body">{$err['body']}</article>

	<div class="card-footer" style="display:flex; justify-content: space-between;">
		<div>
			{$err['foot']}
		</div>
		<div>
			<a class="btn btn-outline-primary" href="javascript:history.back();"><i class="fas fa-arrow-left"></i> Go Back</a>
			<a class="btn btn-outline-secondary" href="/"><i class="fas fa-home"></i> Start Over</a>
		</div>
	</div>

</div>
</div>

</main>

<footer>
	<a href="https://openthc.com/">openthc.com</a>
	<a href="https://openthc.com/about/tos">Terms of Service</a>
	<a href="https://openthc.com/about/privacy">Privacy</a>
</footer>

</body>
</html>
HTML;

	__exit_html($html, $code);
}
