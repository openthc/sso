<?php
/**
 * Shut
 */

namespace App\Controller\Auth;

class Shut extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Shut Will Bounce you Back Out
		$r = $_SERVER['HTTP_REFERER'];
		$h = parse_url($r, PHP_URL_HOST);
		if (!empty($h)) {
			$r = sprintf('https://%s', $h);
		} else {
			$r = '/';
		}

		$_SESSION = [];

		$sn = session_name();
		$sp = session_get_cookie_params();
		// Rewrite this array key for PHP
		$sp['expires'] = $sp['lifetime'];
		unset($sp['lifetime']);
		setcookie($sn, '', $sp);

		session_destroy();
		session_write_close();

		$file = 'page/done.html';

		$data = [];
		$data['Page'] = [ 'title' => 'Session Closed' ];
		$data['body'] = '<p>Your session has been closed</p><p>';
		$data['foot'] = '<a class="btn btn-outline-secondary" href="/auth/open">Sign In Again</a>';

		return $this->_container->view->render($RES, $file, $data);

	}
}
