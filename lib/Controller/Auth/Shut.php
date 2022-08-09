<?php
/**
 * Shut
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Auth;

class Shut extends \OpenTHC\Controller\Auth\Shut
{
	function __invoke($REQ, $RES, $ARG)
	{
		// If parent class gives error we should do something smarter
		$resX = parent::__invoke($REQ, $RES, $ARG);
		if (200 != $resX->getStatusCode()) {
			return $resX;
		}

		if (!empty($_GET['r'])) {
			return $RES->withRedirect($_GET['r']);
		}

		$data = [];
		$data['Page'] = [ 'title' => 'Session Closed' ];
		$data['body'] = '<p>Your session has been closed</p><p>';
		$data['foot'] = '<a class="btn btn-outline-secondary" href="/auth/open">Sign In Again</a>';

		return $RES->write( $this->render('done.php', $data) );

	}
}
