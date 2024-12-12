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
	$RES = new \OpenTHC\SSO\Response(200);
	$RES = $RES->withHeader('content-type', 'text/html; charset=utf-8');
	return $RES;
};

// API Stuff
$app->group('/api', 'OpenTHC\SSO\Module\API');

// Account v0
$app->group('/account', 'OpenTHC\SSO\Module\Account')->add('OpenTHC\Middleware\Session');
// Profile v1
$app->group('/profile', 'OpenTHC\SSO\Module\Account')->add('OpenTHC\Middleware\Session');

// Authentication Routes
$app->group('/auth', function() {

	$this->get('/open', 'OpenTHC\SSO\Controller\Auth\Open')->setName('auth/open');
	$this->post('/open', 'OpenTHC\SSO\Controller\Auth\Open:post')->setName('auth/open:post');

	$this->get('/once', 'OpenTHC\SSO\Controller\Auth\Once')->setName('auth/once');

	$this->map(['GET','POST'], '/init', 'OpenTHC\SSO\Controller\Auth\Init')->setName('auth/init');

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


// Company
$app->group('/company', function() {

	// $this->get('/create', 'OpenTHC\SSO\Controller\Account\Company');
	// $this->post('/create', 'OpenTHC\SSO\Controller\Account\Company:post');

	$this->get('/join', 'OpenTHC\SSO\Controller\Company\Join');

})->add('OpenTHC\Middleware\Session');


// Service
$app->group('/service', function() {

	$this->get('/connect/{svc}', 'OpenTHC\SSO\Controller\Service\Connect');

})->add('OpenTHC\Middleware\Session');


// Verification Steps
$app->group('/verify', 'OpenTHC\SSO\Module\Verify')->add('OpenTHC\Middleware\Session');


// Notification
$app->group('/notify', function ($grp) {
		$grp->get('[/{notify_id}]', 'OpenTHC\SSO\Controller\Notify')->setName('notify');
		$grp->post('[/{notify_id}]', 'OpenTHC\SSO\Controller\Notify:post')->setName('notify:post');
})
	->add('OpenTHC\Middleware\Session')
;

// the Done/Stop Page
$app->get('/done', 'OpenTHC\SSO\Controller\Done')->setName('done')
	->add('OpenTHC\Middleware\Session');;


// Enable Test Options
// $app->add('OpenTHC\Middleware\Test');


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Go!
$app->run();

exit(0);
