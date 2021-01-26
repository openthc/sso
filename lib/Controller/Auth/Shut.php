<?php
/**
 * Shut
 */

namespace App\Controller\Auth;

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

		$file = 'page/done.html';

		$data = [];
		$data['Page'] = [ 'title' => 'Session Closed' ];
		$data['body'] = '<p>Your session has been closed</p><p>';
		$data['foot'] = '<a class="btn btn-outline-secondary" href="/auth/open">Sign In Again</a>';

		$RES = $this->_container->view->render($RES, $file, $data);

		return $RES;

	}
}
