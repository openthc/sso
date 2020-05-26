<?php
/**
 * Enables Test Mode
 */

namespace App\Middleware;

class TestMode extends \OpenTHC\Middleware\Base
{
	function __invoke($REQ, $RES, $NMW) {

		$test = false;

		if (!empty($_COOKIE['test'])) {
			$test = true;
		}

		if (!empty($_GET['_t'])) {
			$key = \OpenTHC\Config::get('application_test.secret');
			if ($_GET['_t'] == $key) {
				$test = true;
				setcookie('test', 'true', 0, '/', '', true, true);
			}
		}

		if ($test) {
			$_ENV['test'] = $test;
		}

		$RES = $NMW($REQ, $RES);

		if ($test) {
			$RES = $RES->withHeader('openthc-test-mode', '1');
		}

		return $RES;

	}
}
