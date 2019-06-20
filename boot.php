<?php
/**
 * OpenTHC Auth Bootstrap
 */

define('APP_NAME', 'OpenTHC | Auth');
define('APP_ROOT', __DIR__);
define('APP_SALT', '01DDS3VMHTWT05SMAPFB8DBW5G');

define('APP_BUILD', '420.19.053');

error_reporting(E_ALL & ~ E_NOTICE);

openlog('openthc-ops', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');
