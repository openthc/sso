<?php
/**
 * Handles CSRF / DUR Tokens
 */

namespace App;

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
			} elseif ($age <= 0) {
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
			return false;
			__exit_text('Invalid Request [ALC-072]', 400);
		}

		$tok = $_SESSION['_csrf'][$key];
		if (empty($tok)) {
			return false;
			__exit_text('Invalid Request [ALC-077]', 400);
		}

		$age = $tok['expires_at'] - $_SERVER['REQUEST_TIME'];
		if ($age > 0) {
			if ($and_clear) {
				unset($_SESSION['_csrf'][$key]);
			}
			return true;
		}

		return false;
	}

}
