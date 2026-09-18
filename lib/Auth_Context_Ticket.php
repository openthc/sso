<?php
/**
 * An Authentication Context Ticket
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO;

class Auth_Context_Ticket // extends \OpenTHC\Auth_Context_Ticket
{
	private $redis_key_prefix = '/sso/auth/ticket';

	/**
	 *
	 */
	static function get($tok)
	{
		$rdb = \OpenTHC\Service\Redis::factory();
		$key = sprintf('%s/%s', $this->redis_key_prefix, $tok);
		$ret = $rdb->get($key);
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
		$key = sprintf('%s/%s', $this->redis_key_prefix, $tok);
		$res = $rdb->set($key, $val, [ 'ex' => $ttl ]);

		return $tok;

	}

}
