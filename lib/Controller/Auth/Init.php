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

		// Auth_Contact
		$sql = 'SELECT id, company_id, username, password, flag FROM auth_contact WHERE id = :pk';
		$arg = [ ':pk' => $_SESSION['Contact']['id'] ];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#023]', 400);
		}
		$Contact = $chk;

		// Contact
		$sql = 'SELECT id, name, phone, email FROM contact WHERE id = :pk';
		$arg = [ ':pk' => $_SESSION['Contact']['id'] ];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#033]', 400);
		}

		$Contact = array_merge($Contact, $chk);

		if (0 != ($Contact['flag'] & Contact::FLAG_DISABLED)) {
			_exit_text('Invalid Account [CAI#038]', 403);
		}

		if (0 == ($Contact['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			$val = [ 'contact' => $Contact ];
			$val = json_encode($val);
			$arg = _encrypt($val, $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/verify?_=' . $arg);
		}

		if (0 == ($Contact['flag'] & Contact::FLAG_PHONE_GOOD)) {
			$val = [ 'contact' => $Contact ];
			$val = json_encode($val);
			$arg = _encrypt($val, $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/verify?_=' . $arg);
		}

		// Company
		$sql = 'SELECT id, name, cre FROM company WHERE id = :pk';
		$arg = [ ':pk' => $Contact['company_id'] ];
		$chk = $dbc->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#051]', 400);
		}
		$Company = $chk;
		$_SESSION['gid'] = $Company['id'];

		if (!empty($_SESSION['return-link'])) {
			$ret = $_SESSION['return-link'];
			unset($_SESSION['return-link']);
		}

		// No Return? Load Default
		if (empty($ret)) {

			$hash = base64_encode_url(hash('sha256', openssl_random_pseudo_bytes(256), true));
			$data = json_encode([
				'contact' => [
					'id' => $Contact['id'],
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
