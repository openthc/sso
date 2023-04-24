<?php
/**
 * An Authentication Context Ticket
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO;

class Auth_Context_Ticket extends \OpenTHC\Auth_Context_Ticket
{
	static function get($k)
	{
		$rdb = \OpenTHC\Service\Redis::factory();
		$ret = $rdb->get(sprintf('/auth-ticket/%s', $v));
		$ret = json_decode($ret, true);
		return $ret;
	}

	/**
	 *
	 */
	static function set($v, $t=420)
	{
		if (is_array($v)) {
			$v = json_encode($v);
		}

		$rdb = \OpenTHC\Service\Redis::factory();

		$tok = _random_hash();
		$res = $rdb->set(sprintf('/auth-ticket/%s', $tok), $val, [ 'ex' => '420' ]);

		return $tok;

	}

}
