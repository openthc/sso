<?php
/**
 * OpenTHC SSO Front Controller
 */

// We may start with an error code from the PHP interpreter
$e0 = error_get_last();

require_once('../boot.php');

$cfg = [];
$cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);


// App Container
$con = $app->getContainer();
$con['DB'] = function() {
	$cfg = \OpenTHC\Config::get('database');
	$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
};


// Custom Response Object
$con['response'] = function() {
	$RES = new App\Response(200);
	$RES = $RES->withHeader('content-type', 'text/html; charset=utf-8');
	return $RES;
};


// Authentication Routes
$app->group('/auth', function() {

	$this->get('/open', 'App\Controller\Auth\Open');
	$this->post('/open', 'App\Controller\Auth\Open:post');

	$this->get('/once', 'App\Controller\Auth\Once');
	$this->post('/once', 'App\Controller\Auth\Once:post');

	$this->get('/init', 'App\Controller\Auth\Init');

	// $this->get('/ping', 'App\Controller\Auth\Ping');
	$this->get('/ping', function() {
		_exit_text([
			'_COOKIE' => $_COOKIE,
			'_SESSION' => $_SESSION,
		]);
	});

	$this->get('/done', 'App\Controller\Auth\Done');

	$this->get('/shut', 'App\Controller\Auth\Shut');

})->add('OpenTHC\Middleware\Session');


// oAuth2 Routes
$app->group('/oauth2', function() {

	$this->post('/token', 'App\Controller\oAuth2\Token');

	$this->get('/authorize', 'App\Controller\oAuth2\Authorize');
	$this->get('/permit', 'App\Controller\oAuth2\Permit');
	$this->get('/reject', 'App\Controller\oAuth2\Reject');

	$this->get('/profile', 'App\Controller\oAuth2\Profile');

})->add('OpenTHC\Middleware\Session');


// Account
$app->group('/account', function() {

	$this->get('/create', 'App\Controller\Account\Create');
	$this->post('/create', 'App\Controller\Account\Create:post');

	$this->get('/password', 'App\Controller\Account\Password');
	$this->post('/password', 'App\Controller\Account\Password:post');

	$this->get('/verify', 'App\Controller\Account\Verify');

})->add('OpenTHC\Middleware\Session');


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php');
if (is_file($f) {
	require_once($f);
}


// Go!
$app->run();

exit(0);
