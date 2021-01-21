<?php
/**
 * OpenTHC SSO Front Controller
 */

// We may start with an error code from the PHP interpreter
$e0 = error_get_last();

// Early Error Handler
$ef = function($ex, $em=null, $ef=null, $el=null, $ec=null) {

	while (ob_get_level() > 0) { ob_end_clean(); }

	header('HTTP/1.1 500 Internal Error', true, 500);
	header('content-type: text/plain');

	$msg = [];
	if (is_object($ex)) {
		$msg[] = 'Internal Error [SWF-019]';
		$msg[] = $ex->getMessage();
		// $msg[] = $ex->__toString();
	} else {
		$msg[] = 'Internal Error [SWF-022]';
		$msg[] = sprintf('Error: #%d:%s', $ex, $em);
		if (!empty($ef)) {
			$ef = substr($ef, strlen($ef) / 2); // don't show full path
			$msg[] = sprintf('File: ...%s:%d', $ef, $el);
		}
	}

	error_log(implode('; ', $msg));

	echo implode("\n", $msg);
	echo "\n";

	// debug_print_backtrace();

	exit(1);

};

// Early Error Exception Handlers
set_error_handler($ef, (E_ALL & ~ E_NOTICE));
set_exception_handler($ef);

// Load Bootstrapper
require_once('../boot.php');

$cfg = [];
// $cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);

// App Container
$con = $app->getContainer();
// Use our Error Handler
unset($con['errorHandler']);
unset($con['phpErrorHandler']);

// Database Connections
$con['DBC_AUTH'] = function() {
	// $url = getenv('OPENTHC_POSTGRES_URL'):
	$cfg = \OpenTHC\Config::get('database/auth');
	$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
};
$con['DBC_MAIN'] = function() {
	// $url = getenv('OPENTHC_POSTGRES_URL'):
	$cfg = \OpenTHC\Config::get('database/main');
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

	$this->get('/open', 'App\Controller\Auth\Open')->setName('auth/open');
	$this->post('/open', 'App\Controller\Auth\Open:post')->setName('auth/open/post');

	$this->get('/once', 'App\Controller\Auth\Once');
	$this->post('/once', 'App\Controller\Auth\Once:post');

	$this->map(['GET','POST'], '/init', 'App\Controller\Auth\Init');

	// $this->get('/ping', 'App\Controller\Auth\Ping');
	$this->get('/ping', function($REQ, $RES) {
		return $RES->withJSON([
			'_COOKIE' => $_COOKIE,
			'_SESSION' => $_SESSION,
		]);
	});

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

	$this->get('', 'App\Controller\Account\Profile');

	$this->get('/create', 'App\Controller\Account\Create');
	$this->post('/create', 'App\Controller\Account\Create:post')->setName('account/create');

	$this->get('/password', 'App\Controller\Account\Password');
	$this->post('/password', 'App\Controller\Account\Password:post')->setName('account/password/update');

	$this->get('/verify', 'App\Controller\Account\Verify')->setName('account/verify');
	$this->post('/verify', 'App\Controller\Account\Verify:post')->setName('account/verify/update');

})->add('OpenTHC\Middleware\Session');


// the Done/Stop Page
$app->get('/done', 'App\Controller\Done')
	->add('OpenTHC\Middleware\Session');;


// Enable Test Options
// $app->add('App\Middleware\TestMode');


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Go!
$app->run();

exit(0);
