<?php
/**
 * OpenTHC Auth Front Controller
 */

use Edoceo\Radix;
use Edoceo\Radix\Session;

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
$cfg = array('debug' => true);
$app = new \OpenTHC\App($cfg);

// Authentication
$app->group('/auth', function() {

	$this->get('', 'App\Controller\Auth\Open');
	$this->get('/open', 'App\Controller\Auth\Open');

	// $this->get('/back', 'App\Controller\Auth\Back');
	$this->get('/fail', 'App\Controller\Auth\Fail');
	$this->get('/ping', 'App\Controller\Auth\Ping');
	$this->get('/shut', 'App\Controller\Auth\Shut');
	$this->get('/init', 'App\Controller\Auth\Init');

})->add('OpenTHC\Middleware\Session');


$ret = $app->run();

exit(0);
