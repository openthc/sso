<?php
/**
 * OpenTHC SS Configuration Example
 */

$cfg = [];

$cfg['app'] = [
	'id' => '',
];

$cfg['database'] = [
	'auth' => [
		'hostname' => 'localhost',
		'username' => 'openthc_auth',
		'database' => 'openthc_auth',
		'password' => 'openthc_auth',
	],
	'main' => [
		'hostname' => 'localhost',
		'username' => 'openthc_main',
		'database' => 'openthc_main',
		'password' => 'openthc_main',
	]
];


$cfg['openthc'] = [
	'app' => [
		'hostname' => 'app.openthc.dev',
	],
	'dir' => [
		'hostname' => 'dir.openthc.dev'
	]
];

$cfg['maxmind'] = [
	'account' => '',
	'license' => '',
	'license-key' => '',
];

return $cfg;
