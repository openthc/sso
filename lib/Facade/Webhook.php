<?php
/*
 * Webhook Helper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Facade;

class Webhook {

	/**
	 * Calls the Webhook, Ignores Response
	 */
	static function emit(string $action, array $ctx) : void {

		$url = \OpenTHC\Config::get('webhook/url');
		if (empty($url)) {
			return;
		}

		// PreShared Key
		$psk = \OpenTHC\Config::get('webhook/psk');

		$req = _curl_init($url);
		$arg = [];
		$arg['action'] = $action;
		$arg['context'] = $ctx;
		$arg = json_encode($arg);

		// From Common
		_curl_post_json($url, $arg, [
			'authorization' => sprintf('openthc-psk %s', $psk)
		]);

	}

}
