<?php
/**
 * Initialize an Authenticated Session
 */

namespace App\Controller\Auth;

use Edoceo\Radix\Session;

use App\Contact;

class Init extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc = $this->_container->DB;

		// Contact
		$sql = 'SELECT id, company_id, username, password, flag FROM auth_contact WHERE id = :pk';
		$arg = [ ':pk' => $_SESSION['uid'] ];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#016]', 400);
		}

		if (0 != ($chk['flag'] & Contact::FLAG_DISABLED)) {
			_exit_text('Invalid Account [CAI#038]', 403);
		}

		if (0 == ($chk['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			_exit_text('Validate Email!');
			// return $RES->withRedirect('/account/verify');
		}

		if (0 == ($chk['flag'] & Contact::FLAG_PHONE_GOOD)) {
			// _exit_text('Validate Phone!');
			// return $RES->withRedirect('/account/verify');
		}

		$Contact = $chk;
		$_SESSION['email'] = $Contact['username'];

		// Company
		$sql = 'SELECT id, name FROM company WHERE id = :pk';
		$arg = [ ':pk' => $Contact['company_id'] ];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#016]', 400);
		}
		$Company = $chk;
		$_SESSION['gid'] = $Company['id'];

		if (!empty($_SESSION['return-link'])) {
			$ret = $_SESSION['return-link'];
			unset($_SESSION['return-link']);
		}

		// No Return? Load Default
		if (empty($ret)) {

			$hash = sha1(openssl_random_pseudo_bytes(256));
			$data = json_encode([
				'contact' => [
					'id' => $_SESSION['uid'],
					'username' => $Contact['username'],
					'password' => $Contact['password'],
				],
				'company' => $Company,
			]);

			// file_put_contents(sprintf('%s/var/%s.json', APP_ROOT, $hash), $data);
			$R = new \Redis();
			$R->connect('127.0.0.1');
			$R->set($hash, $data);

			$ping = sprintf('https://%s/auth/once?a=%s', $_SERVER['SERVER_NAME'], $hash);

			// Bounce Them
			$cfg = \OpenTHC\Config::get('openthc_app');
			$ret = trim($cfg['url'], '/');
			$ret.= '/auth/back?' . http_build_query([ 'ping' => $ping ]);

		}

		return $RES->withRedirect($ret);

	}
}
