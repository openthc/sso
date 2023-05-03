<?php
/**
 * An Authentication Context Ticket
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO;

class Auth_Context_Ticket // extends \OpenTHC\Auth_Context_Ticket
{
	/**
	 *
	 */
	static function get($key)
	{
		$rdb = \OpenTHC\Service\Redis::factory();
		$ret = $rdb->get(sprintf('/auth-ticket/%s', $key));
		$ret = json_decode($ret, true);

		// if (empty($ret)) {

		// 	$sql = 'SELECT * FROM auth_context_ticket WHERE id = ?';
		// 	$arg = array($_POST['code']);
		// 	$res = $this->_dbc->fetchRow($sql, $arg);

		// }

		return $ret;
	}

	/**
	 *
	 */
	static function set($val, $ttl=420)
	{
		if (is_array($val)) {
			$val = json_encode($val, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

		$rdb = \OpenTHC\Service\Redis::factory();

		$tok = _random_hash();
		$res = $rdb->set(sprintf('/auth-ticket/%s', $tok), $val, [ 'ex' => $ttl ]);

		return $tok;

	}

}
