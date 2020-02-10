<?php
/**
 * Set a Password
 */

namespace App\Controller\Account;

class Password extends \OpenTHC\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$ARG = $this->parseArg();

		$file = 'page/account/password.html';
		$data = [];
		$data['Page'] = [ 'title' => 'Set Password' ];
		$data['username'] = $ARG['contact']['username'];

		if (!empty($_GET['e'])) {
			switch ($_GET['e']) {
			case 'cap047':
				$data['Page']['flash'] = 'Invalid password';
				break;
			case 'cap052':
				$data['Page']['flash'] = 'Invalid password';
				break;
			case 'cap057':
				$data['Page']['flash'] = 'Invalid password';
				break;
			case 'cap062':
				$data['Page']['flash'] = 'Passwords do not match';
				break;
			}
		}

		return $this->_container->view->render($RES, $file, $data);

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$ARG = $this->parseArg();

		// Set Their Password
		switch (strtolower($_POST['a'])) {
		case 'update':

			$p = $_POST['p0'];

			if (empty($p) || empty($_POST['p1'])) {
				return $RES->withRedirect('/account/password?e=cap047');
			}

			if (strlen($p) < 8) {
				return $RES->withRedirect('/account/password?e=cap052');
			}

			if (preg_match_all('/\w|\d/', $p) < 8) {
				return $RES->withRedirect('/account/password?e=cap057');
			}

			if ($p != $_POST['p1']) {
				return $RES->withRedirect('/account/password?e=cap062');
			}

			$new_password_hash = password_hash($_POST['p0'], PASSWORD_DEFAULT);

			$arg = [];
			$arg[] = $new_password_hash;
			$arg[] = $ARG['contact']['id'];

			$sql = 'UPDATE auth_contact SET password = ? WHERE id = ?';

			$dbc = $this->_container->DB;
			$dbc->query($sql, $arg);

			$RES = $RES->withAttribute('contact-id', $ARG['contact']['id']);
			$RES = $RES->withAttribute('contact-username', $ARG['contact']['username']);
			$RES = $RES->withAttribute('contact-password-hash', $new_password_hash);

			return $RES->withRedirect('/auth/open?e=cap080');

			break;
		}
	}

	private function parseArg()
	{
		$ARG = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$ARG = json_decode($ARG, true);

		if (empty($ARG)) {
			_exit_text('Invalid Request [CAP#019]');
		}

		if (empty($ARG['contact']['id'])) {
			_exit_text('No [CAP#015]', 400);
		}

		return $ARG;
	}
}
