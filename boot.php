<?php
/**
 * OpenTHC SSO Bootstrap
 *
 * SPDX-License-Identifier: MIT
 */

// declare(encoding='UTF-8');
// declare(strict_types=1);

define('APP_ROOT', __DIR__);
define('APP_BUILD', '420.23.244');

error_reporting(E_ALL & ~E_NOTICE);

openlog('openthc-sso', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');

if ( ! \OpenTHC\Config::init(APP_ROOT) ) {
	_exit_html_fail('<h1>Invalid Application Configuration [ALB-035]</h1>', 500);
}

define('OPENTHC_SERVICE_ID', \OpenTHC\Config::get('openthc/sso/id'));
define('OPENTHC_SERVICE_ORIGIN', \OpenTHC\Config::get('openthc/sso/origin'));

_error_handler_init();
