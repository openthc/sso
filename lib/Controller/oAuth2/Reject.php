<?php
/**
 * Reject the Access
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\oAuth2;

class Reject extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		if (empty($_GET['_'])) {
			__exit_text('Invalid Input [COR-013]', 400);
		}

		$x = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$x = json_decode($x, true);
		if (empty($x)) {
			__exit_text('Invalid Input [COR-019]', 400);
		}

		$_GET = $x;

		// Rebuild URL
		if (empty($uri['query'])) {
			$uri['query'] = array();
		} elseif (!empty($uri['query'])) {
			$uri['query'] = __parse_str($uri['query']);
		}

		$uri['query']['error'] = 'rejected';
		$uri['query']['error_description'] = 'No';
		$uri['query']['error_uri'] = sprintf('%s/oauth2/doc', OPENTHC_SERVICE_ORIGIN);
		$uri['query']['state'] = $_GET['state'];
		ksort($uri['query']);

		$uri['query'] = http_build_query($uri['query']);

		$ret = _url_assemble($uri);

		if (\OpenTHC\Config::get('sso/redirect-fast')) {
			return $this->redirect($ret);
		}

	}
}
