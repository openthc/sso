<?php
/**
 * Enables Test Mode
 */

namespace App\Middleware;

class TestMode extends \OpenTHC\Middleware\Base
{
	function __invoke($REQ, $RES, $NMW) {

		$key_user = null;
		$key_real = null;
		$set_test = false;

		if (!empty($_COOKIE['test'])) {
			$key_user = $_COOKIE['test'];
		}

		if (!empty($_GET['_t'])) {
			$key_user = $_GET['_t'];
		}

		if (!empty($key_user)) {
			$key_real = \OpenTHC\Config::get('openthc/test');
			if ($key_user == $key_real) {
				$set_test = true;
			}
		}

		if ($set_test) {
			$_ENV['test'] = $set_test;
			setcookie('test', $key_real, 0, '/', '', true, true);
			$RES = $RES->withHeader('openthc-test-mode', '1');
		}

		$RES = $NMW($REQ, $RES);

		return $RES;

	}
}
