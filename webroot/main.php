<?php
/**
 * OpenTHC SSO Main Controller
 *
 * SPDX-License-Identifier: MIT
 */

$e0 = error_get_last();

// Load Bootstrapper
require_once('../boot.php');

// _error_handler_init([
// 	'hint' => '<h2>You can <a href="javascript:history.go(-1);">go back</a> and try again, or <a href="/auth/open">sign-in again</a>.</h2>'
// ]);

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
	$cfg = \OpenTHC\Config::get('database/auth');
	$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
};
$con['DBC_MAIN'] = function() {
	$cfg = \OpenTHC\Config::get('database/main');
	$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
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

	$this->get('/open', 'OpenTHC\SSO\Controller\Auth\Open')->setName('auth/open');
	$this->post('/open', 'OpenTHC\SSO\Controller\Auth\Open:post')->setName('auth/open/post');

	$this->get('/once', 'OpenTHC\SSO\Controller\Auth\Once');

	$this->map(['GET','POST'], '/init', 'OpenTHC\SSO\Controller\Auth\Init');

	// $this->get('/ping', 'OpenTHC\SSO\Controller\Auth\Ping');
	$this->get('/ping', function($REQ, $RES) {
		return $RES->withJSON([
			'_COOKIE' => $_COOKIE,
			'_SESSION' => $_SESSION,
		]);
	});

	$this->get('/shut', 'OpenTHC\SSO\Controller\Auth\Shut');

})->add('OpenTHC\Middleware\Session');


// oAuth2 Routes
$app->group('/oauth2', function() {

	$this->post('/token', 'OpenTHC\SSO\Controller\oAuth2\Token');

	$this->get('/authorize', 'OpenTHC\SSO\Controller\oAuth2\Authorize');
	$this->get('/permit', 'OpenTHC\SSO\Controller\oAuth2\Permit');
	$this->get('/reject', 'OpenTHC\SSO\Controller\oAuth2\Reject');

	$this->get('/profile', 'OpenTHC\SSO\Controller\oAuth2\Profile');

})->add('OpenTHC\Middleware\Session');


// Account
$app->group('/account', function() {

	$this->get('', 'OpenTHC\SSO\Controller\Account\Profile');
	$this->post('', 'OpenTHC\SSO\Controller\Account\Profile:post');

	$this->get('/create', 'OpenTHC\SSO\Controller\Account\Create');
	$this->post('/create', 'OpenTHC\SSO\Controller\Account\Create:post')->setName('account/create');

	$this->get('/create/company', 'OpenTHC\SSO\Controller\Account\Company');
	$this->post('/create/company', 'OpenTHC\SSO\Controller\Account\Company:post');

	$this->get('/password', 'OpenTHC\SSO\Controller\Account\Password');
	$this->post('/password', 'OpenTHC\SSO\Controller\Account\Password:post')->setName('account/password/update');

})->add('OpenTHC\Middleware\Session');


// Verification Steps
$app->group('/verify', function() {

	$this->get('', 'OpenTHC\SSO\Controller\Verify\Main');

	$this->get('/email', 'OpenTHC\SSO\Controller\Verify\Email');
	$this->post('/email', 'OpenTHC\SSO\Controller\Verify\Email:post');

	$this->get('/password', 'OpenTHC\SSO\Controller\Verify\Password');
	$this->post('/password', 'OpenTHC\SSO\Controller\Verify\Password:post');

	$this->get('/location', 'OpenTHC\SSO\Controller\Verify\Location');
	$this->post('/location', 'OpenTHC\SSO\Controller\Verify\Location:post');

	$this->get('/timezone', 'OpenTHC\SSO\Controller\Verify\Timezone');
	$this->post('/timezone', 'OpenTHC\SSO\Controller\Verify\Timezone:post');

	$this->get('/phone', 'OpenTHC\SSO\Controller\Verify\Phone');
	$this->post('/phone', 'OpenTHC\SSO\Controller\Verify\Phone:post');

	$this->get('/company', 'OpenTHC\SSO\Controller\Verify\Company');
	$this->post('/company', 'OpenTHC\SSO\Controller\Verify\Company:post');

	$this->get('/license', 'OpenTHC\SSO\Controller\Verify\License');
	$this->post('/license', 'OpenTHC\SSO\Controller\Verify\License:post');

})->add('OpenTHC\Middleware\Session');


// the Done/Stop Page
$app->get('/done', 'OpenTHC\SSO\Controller\Done')
	->add('OpenTHC\Middleware\Session');;


// Enable Test Options
$app->add('OpenTHC\SSO\Middleware\TestMode');


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Go!
$app->run();

exit(0);
