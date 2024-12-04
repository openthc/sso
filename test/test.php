#!/usr/bin/php
<?php
/**
 * SSO Test Runner
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test;

require_once(dirname(__DIR__) . '/boot.php');

if (empty($_SERVER['argv'][1])) {
	$_SERVER['argv'][1] = 'all';
}


$doc = <<<DOC
OpenTHC SSO Test Runner

Usage:
	test --filter=X

Options:
	--filter=FILTER
	--phpunit-config=FILE      File to use for PHPUnit XML Configuration
	--phpunit-filter=FILTER    Filter to pass to PHPUnit
DOC;

$res = \Docopt::handle($doc, [
	'exit' => false,
	'help' => true,
	'optionsFirst' => true,
]);
$arg = $res->args;
// var_dump($arg);
// if ('all' == $arg['<command>']) {
	$arg['phplint'] = false;
	$arg['phpstan'] = false;
	$arg['phpunit'] = true;
// } else {
// 	$cmd = $arg['<command>'];
// 	$arg[$cmd] = true;
// 	unset($arg['<command>']);
// }
// var_dump($arg);
// exit;

$dt0 = new \DateTime();

define('OPENTHC_TEST_OUTPUT_BASE', \OpenTHC\Test\Helper::output_path_init());
// define('OPENTHC_TEST_OUTPUT_BASE', '/opt/openthc/sso/webroot/output/test-report');

// PHPLint
if ($arg['phplint']) {
	$tc = new \OpenTHC\Test\Facade\PHPLint([
		'output' => OPENTHC_TEST_OUTPUT_BASE
	]);
	$res = $tc->execute();
	var_dump($res);
}


// PHPStan
if ($arg['phpstan']) {
	$tc = new \OpenTHC\Test\Facade\PHPStan([
		'output' => OPENTHC_TEST_OUTPUT_BASE
	]);
	$res = $tc->execute();
	var_dump($res);
}


// PHPUnit
if ($arg['phpunit']) {
	$cfg = [
		'output' => OPENTHC_TEST_OUTPUT_BASE
	];
	// Pick Config File
	$cfg_file_list = [];
	$cfg_file_list[] = sprintf('%s/phpunit.xml', __DIR__);
	$cfg_file_list[] = sprintf('%s/phpunit.xml.dist', __DIR__);
	foreach ($cfg_file_list as $f) {
		if (is_file($f)) {
			$cfg['--configuration'] = $f;
			break;
		}
	}
	// Filter?
	if ( ! empty($arg['--filter'])) {
		$cfg['--filter'] = $arg['--filter'];
	}
	$tc = new \OpenTHC\Test\Facade\PHPUnit($cfg);
	$res = $tc->execute();
	var_dump($res);
}


// Done
\OpenTHC\Test\Helper::index_create($html);


// Output Information
$origin = \OpenTHC\Config::get('openthc/sso/origin');
$output = str_replace(sprintf('%s/webroot/', APP_ROOT), '', OPENTHC_TEST_OUTPUT_BASE);

echo "TEST COMPLETE\n  $origin/$output\n";
