<?php
/**
 * Initialize an Authenticated Session
 */

namespace App\Controller\Auth;

use Edoceo\Radix\Session;

use App\Contact;

class Init extends \OpenTHC\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// Auth_Contact
		$sql = 'SELECT id, username, password, flag FROM auth_contact WHERE id = :pk';
		$arg = [ ':pk' => $_SESSION['Contact']['id'] ];
		$chk = $dbc_auth->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#023]', 400);
		}
		$Contact = $chk;

		// Contact
		$sql = 'SELECT id, name, phone, email FROM contact WHERE id = :pk';
		$arg = [ ':pk' => $_SESSION['Contact']['id'] ];
		$chk = $dbc_main->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#033]', 400);
		}

		$Contact = array_merge($Contact, $chk);

		// Contact Globally Disabled?
		if (0 != ($Contact['flag'] & Contact::FLAG_DISABLED)) {
			_exit_text('Invalid Account [CAI#038]', 403);
		}
		switch ($Contact['stat']) {
			case 100:
			break;
			case 200:
				// OK
			break;
			case 410:
				_exit_text('Invalid Account [CAI#049]', 403);
			break;
		}

		// Need to Verify
		if (0 == ($Contact['flag'] & Contact::FLAG_EMAIL_GOOD)) {
			$val = [ 'contact' => $Contact ];
			$val = json_encode($val);
			$arg = _encrypt($val, $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/verify?r=/auth/init&_=' . $arg);
		}

		if (0 == ($Contact['flag'] & Contact::FLAG_PHONE_GOOD)) {
			$val = [ 'contact' => $Contact ];
			$val = json_encode($val);
			$arg = _encrypt($val, $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/verify?r=/auth/init&_=' . $arg);
		}

		// Company List
		$sql = 'SELECT id, name, cre FROM auth_company WHERE id IN (SELECT company_id FROM auth_company_contact WHERE contact_id = :c0)';
		$arg = [ ':c0' => $Contact['id'] ];
		$chk = $dbc_auth->fetchAll($sql, $arg);

		// User with 0 Company Link
		switch (count($chk)) {
			case 0:
				_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#051]', 400);
			break;
			case 1:
				$Company = $chk[0];
				return $this->_create_ticket_and_redirect($RES, $Contact, $Company);
			break;
			default:

				// User with Many Company Links AND they picked one
				if (!empty($_POST['company_id'])) {
					foreach ($chk as $company_rec) {
						if ($company_rec['id'] === $_POST['company_id']) {
							$Company = $company_rec;
							return $this->_create_ticket_and_redirect($RES, $Contact, $Company);
							break;
						}
					}
				}

				$data = $this->data;
				$data['Page']['title'] = 'Select Company';
				$data['company_list'] = $chk;
				$RES = $this->_container->view->render($RES, 'page/auth/init.html', $data);
				return $RES->withStatus(300);

		}

		return $RES->withJSON([
			'data' => null,
			'meta' => [ 'detail' => 'Unexpected Server Error [CAI#108] '],
		], 500);

	}

	/**
	 * @return Response ready to be redirected
	 */
	function _create_ticket_and_redirect($RES, $Contact, $Company)
	{
		// Create Auth Ticket
		$hash = _random_hash();
		$data = json_encode([
			'contact' => [
				'id' => $Contact['id'],
				'flag' => $Contact['flag'],
				'username' => $Contact['username'],
			],
			'company' => $Company,
		]);

		$this->_container->Redis->set($hash, $data, 240);

		$ping = sprintf('https://%s/auth/once?_=%s', $_SERVER['SERVER_NAME'], $hash);

		if (!empty($_SESSION['return-link'])) {
			$ret = $_SESSION['return-link'];
			unset($_SESSION['return-link']);
		}

		// No Return? Load Default
		if (empty($ret)) {
			$cfg = \OpenTHC\Config::get('openthc/app');
			$ret = sprintf('https://%s/auth/back?ping={PING}', $cfg['hostname']);
		}

		// Place Ping Back Token
		$ret = str_replace('{PING}', $ping, $ret);

		return $RES->withRedirect($ret);

	}
}
