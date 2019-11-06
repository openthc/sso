<?php
/**
 * Reject the Access
 */

namespace App\Controller\oAuth2;

class Reject extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$_ENV['fast-redirect'] = false;

		// Rebuild URL
		if (empty($uri['query'])) {
			$uri['query'] = array();
		} elseif (!empty($uri['query'])) {
			$uri['query'] = _parse_str($uri['query']);
		}

		$uri['query']['error'] = 'rejected';
		$uri['query']['error_description'] = 'No';
		$uri['query']['error_uri'] = sprintf('https://%s/oauth2/doc', $_SERVER['SERVER_NAME']);
		$uri['query']['state'] = $_GET['state'];
		ksort($uri['query']);

		$uri['query'] = http_build_query($uri['query']);

		$ret = _url_assemble($uri);

		if ($_ENV['fast-redirect']) {
			return $RES->withRedirect($ret);
		}

	}
}
