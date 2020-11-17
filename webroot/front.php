<?php
/**
 * OpenTHC SSO Front Controller
 */

// We may start with an error code from the PHP interpreter
$e0 = error_get_last();

require_once('../boot.php');

$cfg = [];
// $cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);


// App Container
$con = $app->getContainer();
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

// Error Handler
$con['errorHandler'] = function($c0) {

	return function($REQ, $RES, $ERR) use ($c0) {

		$dump = [];
		$dump['note'] = $ERR->getMessage();
		$dump['code'] = $ERR->getCode();
		$dump['file'] = $ERR->getFile();
		$dump['line'] = $ERR->getLine();
		$dump['stack'] = $ERR->getTrace();

		$file = sprintf('/tmp/err%s.json', $_SERVER['UNIQUE_ID']);
		file_put_contents($file, json_encode($dump));

		$RES = new App\Response(500);
		$RES = $RES->withJSON([
			'error' => 'server_error',
			'error_description' => $dump['note'],
			'error_uri' => 'https://openthc.com/err#err063',
			'dump' => $dump,
		]);

		return $RES;

	};
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
