<?php
/**
 * OpenTHC SSO Bootstrap
 */

define('APP_ROOT', __DIR__);
define('APP_BUILD', '420.21.007');

error_reporting(E_ALL & ~E_NOTICE);

openlog('openthc-sso', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');
require_once(APP_ROOT . '/lib/err.php');

_error_handler_init();

\OpenTHC\Config::init(__DIR__);
