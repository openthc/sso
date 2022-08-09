<?php
/**
 * Handles CSRF / DUR Tokens
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO;

class CSRF
{
	const MAX_LIFE = 600;
	const MIN_LIFE = 60;

	/**
	 *
	 */
	static function init()
	{
		if (empty($_SESSION['_csrf'])) {
			$_SESSION['_csrf'] = [];
		} elseif (!is_array($_SESSION['_csrf'])) {
			$_SESSION['_csrf'] = [];
		}

	}

	/**
	 *
	 */
	static function getToken()
	{
		self::init();

		$ret = null;
		$key_list = array_keys($_SESSION['_csrf']);
		foreach ($key_list as $key) {

			$tok = $_SESSION['_csrf'][$key];
			$age = $tok['expires_at'] - $_SERVER['REQUEST_TIME'];

			// Positive Age is Valid
			if ($age > 0) {
				$ret = $key;
				break;
			} else {
				// Invalid
				unset($_SESSION['_csrf'][$key]);
			}

		}

		// Create a Token (if needed)
		if (empty($ret)) {
			$ret = _random_hash();
			$_SESSION['_csrf'][$ret] = [
				'created_at' => $_SERVER['REQUEST_TIME'],
				'expires_at' => ($_SERVER['REQUEST_TIME'] + self::MAX_LIFE),
			];
		}

		return $ret;

	}

	/**
	 * @param string $key -- the key to lookup
	 * @param bool $and_clear -- set to true to expire the key
	 */
	static function verify($key, $and_clear=false)
	{
		self::init();

		if (empty($key)) {
			_exit_html_warn('<h1>Invalid Request [ALC-072]</h1><h2><a href="javascript:history.back();">go back</a></h2>', 400);
			return false;
		}

		$tok = $_SESSION['_csrf'][$key];
		if (empty($tok)) {
			_exit_html_warn('<h1>Invalid Request [ALC-077]</h1><h2><a href="javascript:history.back();">go back</a></h2>', 400);
			return false;
		}

		$age = $tok['expires_at'] - $_SERVER['REQUEST_TIME'];
		if ($age > 0) {
			if ($and_clear) {
				unset($_SESSION['_csrf'][$key]);
			}
			return true;
		}

		__exit_text('Invalid Request [ALC-091]', 400);
		return false;
	}

}
