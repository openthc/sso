<?php
/**
 * OpenTHC SSO Front Controller
 */

// We may start with an error code from the PHP interpreter
$e0 = error_get_last();

header('Cache-Control: no-cache, must-revalidate');
header('Content-Language: en');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY'); // SAMEORIGIN
header('X-XSS-Protection: 1; mode=block');

require_once('../boot.php');

$cfg = [];
$cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);

// App Container
$con = $app->getContainer();
$con['DB'] = function() {
	$cfg = \OpenTHC\Config::get('database_main');
	return new \Edoceo\Radix\DB\SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);
};


// Authentication
$app->group('/auth', function() {

	// $this->get('', 'App\Controller\Auth\Open');
	$this->get('/open', 'App\Controller\Auth\Open');
	$this->post('/open', 'App\Controller\Auth\Open:post');
	// $this->get('/back', 'App\Controller\Auth\Back');
	$this->get('/fail', 'App\Controller\Auth\Fail');
	$this->get('/ping', 'App\Controller\Auth\Ping');
	$this->get('/shut', 'App\Controller\Auth\Shut');
	$this->get('/init', 'App\Controller\Auth\Init');

})->add('OpenTHC\Middleware\Session');


// oAuth2 Routes
$app->group('/oauth2', function() {

	$this->post('/token', 'App\Controller\oAuth2\Token');

	$this->get('/authorize', 'App\Controller\oAuth2\Authorize');
	$this->get('/permit', 'App\Controller\oAuth2\Permit');
	$this->get('/reject', 'App\Controller\oAuth2\Reject');

	$this->get('/profile', 'App\Controller\oAuth2\Profile');

})->add('OpenTHC\Middleware\Session');


// Go!
$ret = $app->run();

exit(0);
