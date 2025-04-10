<?php
/**
 * Service Connect
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Service;

class Connect extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$svc = strtolower($ARG['svc']);
		switch ($svc) {
		case 'app':
			$cfg = \OpenTHC\Config::get('openthc/app');
			$url = sprintf('%s/auth/sso', $cfg['origin']);
			return $RES->withRedirect($url);
		case 'b2b':
			// Something
			$cfg = \OpenTHC\Config::get('openthc/b2b');
			$url = sprintf('%s/auth/open?%s', $cfg['origin'], http_build_query([
				'r' => '/dashboard'
			]));
			return $RES->withRedirect($url);
			break;
		case 'chat':
			// Something
			$cfg = \OpenTHC\Config::get('openthc/chat');
			$url = sprintf('%s/auth/open?%s', $cfg['origin'], http_build_query([
				'r' => '/'
			]));
			return $RES->withRedirect($url);
		case 'dir':
			$cfg = \OpenTHC\Config::get('openthc/dir');
			$url = sprintf('%s/auth/open?%s', $cfg['origin'], http_build_query([
				'r' => sprintf('/company/%s', $_SESSION['Company']['id'])
			]));
			return $RES->withRedirect($url);
			break;
		case 'pos':
			// Requires a Good Company
			// Should be in the POS Code for this (like App)
			if (empty($_SESSION['Company']['cre'])) {
				return $RES->withRedirect('/done?e=CSC-045');
			}
			$cfg = \OpenTHC\Config::get('openthc/pos');
			$url = sprintf('%s/auth/open', $cfg['origin']);
			return $RES->withRedirect($url);
			break;
		}

	}

}
