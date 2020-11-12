<?php
/**
 * Initialize an Authenticated Session
 */

namespace App\Controller\Auth;

use Edoceo\Radix\Session;

use App\Contact;

class Init extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		unset($_SESSION['Contact']);
		unset($_SESSION['Company']);
		unset($_SESSION['License']);
		unset($_SESSION['Service']);

		// Check Input
		if (!preg_match('/^([\w\-]{32,128})$/i', $_GET['_'], $m)) {
			_exit_html('<h1>Invalid Request [<a href="https://openthc.com/err#cai026">CAI#026</a>]</h1>', 400);
		}

		$dbc_auth = $this->_container->DBC_AUTH;

		// Load Auth Ticket
		$act = $dbc_auth->fetchRow('SELECT id, meta FROM auth_context_ticket WHERE id = :t', [ ':t' => $_GET['_'] ]);
		if (empty($act['id'])) {
			_exit_html('<h1>Invalid Request [<a href="https://openthc.com/err#cai034">CAI#034</a>]</h1>', 400);
		}
		$act_prev = json_decode($act_prev, true);
		if (empty($act_prev['contact']['id'])) {
			_exit_html('<h1>Invalid Request [<a href="https://openthc.com/err#cai038">CAI#038</a>]</h1>', 400);
		}
		$Contact = $act_prev['contact'];

		// Auth/Contact
		$sql = 'SELECT id, username, password, flag FROM auth_contact WHERE id = :pk';
		$arg = [ ':pk' => $Contact['id'] ];
		$chk = $dbc_auth->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('<h1>Unexpected Session State [<a href="https://openthc.com/err#cai047">CAI#047</a>]</h1><p>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support.</p>', 400);
		}
		$Contact = $chk;

		$dbc_main = $this->_container->DBC_MAIN;

		// Main/Contact
		$sql = 'SELECT id, name, phone, email FROM contact WHERE id = :pk';
		$arg = [ ':pk' => $Contact['id'] ];
		$chk = $dbc_main->fetchRow($sql, $arg);
		if (empty($chk['id'])) {
			_exit_html('<h1>Unexpected Session State [<a href="https://openthc.com/err#cai058">CAI#058</a>]</h1><p>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support.</p>', 400);
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

		// Contact is Good
		$_SESSION['Contact'] = $Contact;

		// Company List
		$sql = <<<SQL
SELECT auth_company.id
, auth_company.name
, auth_company.cre
, auth_company_contact.stat
, auth_company_contact.created_at
FROM auth_company
JOIN auth_company_contact ON auth_company.id = auth_company_contact.company_id
WHERE auth_company_contact.contact_id = :c0
ORDER BY auth_company_contact.stat, auth_company_contact.created_at ASC
SQL;

		$arg = [ ':c0' => $Contact['id'] ];
		$chk = $dbc_auth->fetchAll($sql, $arg);

		// User with 0 Company Link
		switch (count($chk)) {
			case 0:
				_exit_html('Unexpected Session State<br>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support [CAI#051]', 400);
			break;
			case 1:
				$Company = $chk[0];
				$_SESSION['Company'] = $Company;
				return $this->_create_ticket_and_redirect($RES, $Contact, $Company);
			break;
			default:

				// User with Many Company Links AND they picked one
				if (!empty($_POST['company_id'])) {
					foreach ($chk as $company_rec) {
						if ($company_rec['id'] === $_POST['company_id']) {
							$Company = $company_rec;
							$_SESSION['Company'] = $Company;
							return $this->_create_ticket_and_redirect($RES, $act_prev, $Contact, $Company);
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
	function _create_ticket_and_redirect($RES, $act_prev, $Contact, $Company)
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
			'service' => [],
		]);

		$this->_container->Redis->set($hash, $data, 240);

		$ping = sprintf('https://%s/auth/once?_=%s', $_SERVER['SERVER_NAME'], $hash);

		// if (!empty($_SESSION['return-link'])) {
		// 	$ret = $_SESSION['return-link'];
		// 	unset($_SESSION['return-link']);
		// }

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
