<?php
/**
 * OpenTHC SS0 Configuration Example
 */

$cfg = [];

// Database
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

// OpenTHC Services
$cfg['openthc'] = [
	'app' => [
		'id' => '',
		'origin' => 'https://app.openthc.example.com',
	],
	'b2b' => [
		'id' => '',
		'origin' => 'https://b2b.openthc.example.com'
	],
	'dir' => [
		'id' => '',
		'origin' => 'https://dir.openthc.example.com'
	],
	'lab' => [
		'id' => '',
		'origin' => 'https://lab.openthc.example.com'
	],
	'pos' => [
		'id' => '',
		'origin' => 'https://pos.openthc.example.com'
	],
	'sso' => [
		'id' => '',
		'origin' => 'https://sso.openthc.example.com',
	]
];

// Google Services
$cfg['google'] = [
	'recaptcha_public' => '',
	'recaptcha_secret' => '',
];

// hCaptcha
$cfg['hcaptcha'] = [];

// MaxMind
$cfg['maxmind'] = [
	'account' => '',
	'license' => '',
	'license-key' => '',
];

// OpenCAGEData
$cfg['opencage'] = [
	'project' => '',
	'api-key' => ''
];

return $cfg;
