<?php
/**
 * OpenTHC SSO Main Controller
 *
 * SPDX-License-Identifier: MIT
 */

use Psr\Http\Message\ServerRequestInterface;

$e0 = error_get_last();

// Load Bootstrapper
require_once('../boot.php');

// _error_handler_init([
// 	'hint' => '<h2>You can <a href="javascript:history.go(-1);">go back</a> and try again, or <a href="/auth/open">sign-in again</a>.</h2>'
// ]);

$dic = new \DI\Container();
\Slim\Factory\AppFactory::setContainer($dic);
\Slim\Factory\AppFactory::setResponseFactory(new \OpenTHC\SSO\HTTP\Response\Factory());
$app = \Slim\Factory\AppFactory::create();

// Add Error Handler
$errorMiddleware = $app->addErrorMiddleware(
	displayErrorDetails: true,
	logErrors: true,
	logErrorDetails: true
);
$errorMiddleware->setDefaultErrorHandler(function (
		ServerRequestInterface $request,
		Throwable $exception,
		bool $displayErrorDetails
	) use ($app) {
		$response = $app->getResponseFactory()->createResponse(500);
		$response->getBody()->write($exception->getMessage());
		if ($displayErrorDetails) {
			$response->getBody()->write("\n----\n");
			$response->getBody()->write($exception->getTraceAsString());
			// $p = $exception->getPrevious();
		}
		return $response->withHeader('content-type', 'text/plain');
});


// Database Connections
$dic->set('DBC_AUTH', function() {
	$cfg = \OpenTHC\Config::get('database/auth');
	$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
});
$dic->set('DBC_MAIN', function() {
	$cfg = \OpenTHC\Config::get('database/main');
	$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
	return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
});
$dic->set('RDB', function() {
	return \OpenTHC\Service\Redis::factory();
});

// Custom Response Object that implements withRedirect()
// $con['response'] = function() {
// 	$RES = new \OpenTHC\SSO\Response(200);
// 	$RES = $RES->withHeader('content-type', 'text/html; charset=utf-8');
// 	return $RES;
// };

// $callableResolver = $app->getCallableResolver();

// // Create Request object from globals
// $serverRequestCreator = \Slim\Factory\ServerRequestCreatorFactory::create();
// $request = $serverRequestCreator->createServerRequestFromGlobals();

// // Create Error Handler
// $responseFactory = $app->getResponseFactory();
// $eh = new \Slim\Handlers\ErrorHandler($callableResolver, $responseFactory);


// API Stuff
$app->group('/api', function($g) {

	// $g->get('', 'OpenTHC\SSO\Controller\API\Main');

	$g->post('/company/{company_id}/invite', 'OpenTHC\SSO\Controller\API\Company\Invite')->setName('api/company/invite');

	$g->get('/contact', 'OpenTHC\SSO\Controller\API\Contact\Search')->setName('api/contact/search');

	$g->post('/contact', 'OpenTHC\SSO\Controller\API\Contact\Create')->setName('api/contact/create');
	$g->post('/contact/{id}', 'OpenTHC\SSO\Controller\API\Contact\Update');

	$g->post('/jwt/create', 'OpenTHC\SSO\Controller\API\JWT\Create');
	$g->post('/jwt/verify', 'OpenTHC\SSO\Controller\API\JWT\Verify');
});

$app->get('/profile', 'OpenTHC\SSO\Controller\Account\Profile')->add('OpenTHC\Middleware\Session');
$app->post('/profile', 'OpenTHC\SSO\Controller\Account\Profile:post')->add('OpenTHC\Middleware\Session');

// Account
$app->group('/account', function($g) {

	$g->get('', 'OpenTHC\SSO\Controller\Account\Profile');
	$g->post('', 'OpenTHC\SSO\Controller\Account\Profile:post');

	$g->get('/commit', 'OpenTHC\SSO\Controller\Account\Commit')->setName('account/commit');

	$g->get('/create', 'OpenTHC\SSO\Controller\Account\Create')->setName('account/create');
	$g->post('/create', 'OpenTHC\SSO\Controller\Account\Create:post')->setName('account/create:post');

	$g->get('/password', 'OpenTHC\SSO\Controller\Account\Password');
	$g->post('/password', 'OpenTHC\SSO\Controller\Account\Password:post')->setName('account/password/update');

})->add('OpenTHC\Middleware\Session');

// Authentication Routes
$app->group('/auth', function($g) {

	$g->get('/open', 'OpenTHC\SSO\Controller\Auth\Open')->setName('auth/open');
	$g->post('/open', 'OpenTHC\SSO\Controller\Auth\Open:post')->setName('auth/open:post');

	$g->get('/once', 'OpenTHC\SSO\Controller\Auth\Once')->setName('auth/once');

	$g->map(['GET','POST'], '/init', 'OpenTHC\SSO\Controller\Auth\Init')->setName('auth/init');

	// $this->get('/ping', 'OpenTHC\SSO\Controller\Auth\Ping');
	$g->get('/ping', function($REQ, $RES) {
		return $RES->withJSON([
			'_COOKIE' => $_COOKIE,
			'_SESSION' => $_SESSION,
		]);
	});

	$g->get('/shut', 'OpenTHC\SSO\Controller\Auth\Shut');

})->add('OpenTHC\Middleware\Session');


// oAuth2 Routes
$app->group('/oauth2', function($g) {

	$g->post('/token', 'OpenTHC\SSO\Controller\oAuth2\Token');

	$g->get('/authorize', 'OpenTHC\SSO\Controller\oAuth2\Authorize');
	$g->get('/permit', 'OpenTHC\SSO\Controller\oAuth2\Permit');
	$g->get('/reject', 'OpenTHC\SSO\Controller\oAuth2\Reject');

	$g->get('/profile', 'OpenTHC\SSO\Controller\oAuth2\Profile');

})->add('OpenTHC\Middleware\Session');


// Company
$app->group('/company', function($g) {

	// $g->get('/create', 'OpenTHC\SSO\Controller\Account\Company');
	// $g->post('/create', 'OpenTHC\SSO\Controller\Account\Company:post');

	$g->get('/join', 'OpenTHC\SSO\Controller\Company\Join');

})->add('OpenTHC\Middleware\Session');


// Service
$app->group('/service', function($g) {

	$g->get('/connect/{svc}', 'OpenTHC\SSO\Controller\Service\Connect');

})->add('OpenTHC\Middleware\Session');


// Verification Steps
$app->group('/verify', function($g) {

	$g->get('', 'OpenTHC\SSO\Controller\Verify\Main')->setName('verify/main');

	$g->get('/email', 'OpenTHC\SSO\Controller\Verify\Email')->setName('verify/email');
	$g->post('/email', 'OpenTHC\SSO\Controller\Verify\Email:post')->setName('verify/email:post');

	$g->get('/password', 'OpenTHC\SSO\Controller\Account\Password')->setName('verify/password');
	$g->post('/password', 'OpenTHC\SSO\Controller\Account\Password:post');

	$g->get('/location', 'OpenTHC\SSO\Controller\Verify\Location')->setName('verify/location');
	$g->post('/location', 'OpenTHC\SSO\Controller\Verify\Location:post');

	$g->get('/timezone', 'OpenTHC\SSO\Controller\Verify\Timezone')->setName('verify/timezone');
	$g->post('/timezone', 'OpenTHC\SSO\Controller\Verify\Timezone:post');

	$g->get('/phone', 'OpenTHC\SSO\Controller\Verify\Phone')->setName('verify/phone');
	$g->post('/phone', 'OpenTHC\SSO\Controller\Verify\Phone:post')->setName('verify/phone:post');

	$g->get('/company', 'OpenTHC\SSO\Controller\Verify\Company')->setName('verify/company');
	$g->post('/company', 'OpenTHC\SSO\Controller\Verify\Company:post')->setName('verify/company:post');

})->add('OpenTHC\Middleware\Session');


// Notification
$app->group('/notify', function ($g) {
	$g->get('[/{notify_id}]', 'OpenTHC\SSO\Controller\Notify')->setName('notify');
	$g->post('[/{notify_id}]', 'OpenTHC\SSO\Controller\Notify:post')->setName('notify:post');
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
