<?php
/**
 * OpenTHC Auth Front Controller
 */

// We may start with an error code from the PHP interpreter
$e0 = error_get_last();
$t0 = microtime(true);

header('Cache-Control: no-cache, must-revalidate');
header('Content-Language: en');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Mangle SERVER data
require_once(dirname(dirname(__FILE__)) . '/boot.php');

$cfg = array();
$cfg = array('debug' => false);
$app = new \OpenTHC\App($cfg);

// Authentication
$app->group('/auth', function() {

	// $this->get('', 'App\Controller\Auth\Open');
	$this->get('/open', 'App\Controller\Auth\Open');
	// $this->get('/back', 'App\Controller\Auth\Back');
	$this->get('/fail', 'App\Controller\Auth\Fail');
	$this->get('/ping', 'App\Controller\Auth\Ping');
	$this->get('/shut', 'App\Controller\Auth\Shut');
	$this->get('/init', 'App\Controller\Auth\Init');

})->add('OpenTHC\Middleware\Session');

$app->group('/oauth2', function() {

	$this->post('/token', 'App\Controller\oAuth2\Token');

	$this->get('/authorize', 'App\Controller\oAuth2\Authorize');
	$this->get('/permit', 'App\Controller\oAuth2\Accept');
	$this->get('/reject', 'App\Controller\oAuth2\Reject');

	$this->get('/profile', 'App\Controller\oAuth2\Profile');

});


$ret = $app->run();

exit(0);
