<?php
/**
 * Quick Exit Error Handler
 */
function _err_exit_html($err, $code=500)
{
	if (is_string($err)) {
		$err = [
			'code' => 'Error',
			'head' => 'System Error',
			'body' => $err,
		];
	}

	// Add a Link to the Error Code
	if (preg_match('/\[(\w{3}\-\w{3})\]/', $err['body'], $m)) {
		$rem = $m[0];
		$tag = $m[1];
		$rep = sprintf('[<a href="https://openthc.com/err#%s" target="_blank">%s</a>]', $tag, $tag);
		$err['body'] = str_replace($rem, $rep, $err['body']);
	}

	$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#069420">
<title>OpenTHC :: {$err['head']}</title>
<style>
* {
	box-sizing: border-box;
}
body {
	background: #111111;
	display: flex;
	flex-direction: column;
	font-family: sans-serif;
	font-size: 100%;
	height: 100vh;
	margin: 0;
	padding: 0;
	width: 100%;
}
a:hover {
	color: #069420;
}
header {
	flex: 1 1 auto;
}
header h1 {
	color: #f0f0f0;
	margin: 0;
	padding: 0.50rem;
	text-align: center;
}
main {
	flex: 1 1 100%;
}
main > article {
	background: #f0f0f0;
	border: 1px solid #333;
	border-radius: 0.50rem;
	font-size: 200%;
	margin: 10vh auto 0 auto;
	max-width: 800px;
	padding: 1rem 2rem;
}

main > article > footer {
	display: flex;
	font-size: 80%;
	justify-content: space-between;
}
main > article > footer a {
	border: 1px solid #333;
	border-radius: 0.25rem;
	line-height: 1.5;
	margin: 0;
	padding: .375rem .75rem;;
	text-align: center;
	user-select: none;
	vertical-align: middle;
}
body > footer {
	align-content: space-between;
	align-items: center;
	background: #343a40;
	border-top: 2px solid #069420;
	display: flex;
	flex-direction: row;
	flex: 0 1 auto;
	justify-content: space-around;
	margin: 0;
	padding: 0.50rem;
}
body > footer a {
	color: #f9f9f9;
	text-decoration: none;
}
</style>
</head>
<body>
<header>
	<h1 class="card-header">{$err['head']}</h1>
</header>
<main>

	<article>

		{$err['body']}

		<hr>

		<footer>
			<a class="btn btn-outline-primary" href="javascript:history.back();">Go Back</a>
			<a class="btn btn-outline-secondary" href="/">Start Over</a>
		</footer>

	</article>

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
