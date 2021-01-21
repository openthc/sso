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
			_exit_html_err('<h1>Invalid Request [CAI-026]</h1>', 400);
		}

		$dbc_auth = $this->_container->DBC_AUTH;

		// Load Auth Ticket
		$act = $dbc_auth->fetchRow('SELECT id, meta FROM auth_context_ticket WHERE id = :t', [ ':t' => $_GET['_'] ]);
		if (empty($act['id'])) {
			_exit_html_err('<h1>Invalid Request [CAI-034]</a></h1>', 400);
		}
		$act_data = json_decode($act['meta'], true);
		if (empty($act_data['contact']['id'])) {
			_exit_html_err('<h1>Invalid Request [CAI-038]</h1>', 400);
		}

		$Contact = $act_data['contact'];
		// $Contact = $this->_inflate_contact($Contact);

		// Contact Globally Disabled?
		if (0 != ($Contact['flag'] & Contact::FLAG_DISABLED)) {
			_exit_html_err('Invalid Account [CAI-046]', 403);
		}
		switch ($Contact['stat']) {
			case 100:
			break;
			case 200:
				// OK
			break;
			case 410:
				_exit_html_err('Invalid Account [CAI-049]', 403);
			break;
		}

		// Need to Verify
		$f1 = (Contact::FLAG_EMAIL_GOOD | Contact::FLAG_PHONE_GOOD);
		if (($Contact['flag'] & $f1) != $f1) {
			$val = [ 'contact' => $Contact ];
			$val = json_encode($val);
			$arg = _encrypt($val, $_SESSION['crypt-key']);
			return $RES->withRedirect('/account/verify?' . http_build_query([
				'r' => sprintf('/auth/init?_=%s', $_GET['_']),
				'_' => $arg
			]));
		}

		// User with 0 Company Link
		switch (count($act_data['company_list'])) {
			case 0:
				_exit_html_err('<h1>Unexpected Session State [CAI-051]</h1><p>You may want to <a href="/auth/shut">close your session</a> and try again.</p><p>If the issue continues, contact support</p>', 400);
			break;
			case 1:
				$Company = $act_data['company_list'][0];
				return $this->_create_ticket_and_redirect($RES, $act_data, $Contact, $Company);
			break;
			default:

				// User with Many Company Links AND they picked one
				if (!empty($_POST['company_id'])) {
					foreach ($act_data['company_list'] as $c) {
						if ($c['id'] === $_POST['company_id']) {
							$Company = $c;
							return $this->_create_ticket_and_redirect($RES, $act_data, $Contact, $Company);
							break;
						}
					}
				}

				$data = $this->data;
				$data['Page']['title'] = 'Select Company';
				$data['company_list'] = $act_data['company_list'];
				$RES = $this->_container->view->render($RES, 'page/auth/init.html', $data);
				return $RES->withStatus(300);

		}

		return $RES->withJSON([
			'data' => null,
			'meta' => [ 'detail' => 'Unexpected Server Error [CAI-108] '],
		], 500);

	}

	/**
	 * @return Response ready to be redirected
	 */
	function _create_ticket_and_redirect($RES, $act_data, $Contact, $Company)
	{
		$_SESSION['Contact'] = $Contact;
		$_SESSION['Company'] = $Company;

		$act_data['company'] = $Company;

		$act = [];
		$act['id'] = _random_hash();
		$act['meta'] = json_encode($act_data);

		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_auth->insert('auth_context_ticket', $act);

		$ping = sprintf('https://%s/auth/once?_=%s', $_SERVER['SERVER_NAME'], $act['id']);

		// No Return? Load Default
		$ret = null;
		switch ($act_data['intent']) {
			case 'init':
				// Default
			break;
			case 'oauth-authorize':
				$ret = '/oauth2/authorize?' . http_build_query($act_data['oauth-request']);
			break;
		}

		if (empty($ret)) {
			$cfg = \OpenTHC\Config::get('openthc/app/hostname');
			if (!empty($cfg)) {
				$ret = sprintf('https://%s/auth/back?ping={PING}', $cfg);
			}
		}

		if (empty($ret)) {
			$ret = '/profile';
		}

		// Place Ping Back Token
		$ret = str_replace('{PING}', $ping, $ret);

		return $RES->withRedirect($ret);

	}

	/**
	 * Inflate Contact from Auth & Main
	 */
	function _inflate_contact($Contact)
	{
		// Auth/Contact
		// $sql = 'SELECT id, username, password, flag FROM auth_contact WHERE id = :pk';
		// $arg = [ ':pk' => $act_data['contact']['id'] ];
		// $chk = $dbc_auth->fetchRow($sql, $arg);
		// if (empty($chk['id'])) {
		// 	_exit_html_err('<h1>Unexpected Session State [CAI-047]</h1><p>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support.</p>', 400);
		// }
		// $Contact = $chk;
		// $dbc_main = $this->_container->DBC_MAIN;

		// // Main/Contact
		// $sql = 'SELECT id, name, phone, email FROM contact WHERE id = :pk';
		// $arg = [ ':pk' => $Contact['id'] ];
		// $chk = $dbc_main->fetchRow($sql, $arg);
		// if (empty($chk['id'])) {
		// 	_exit_html_err('<h1>Unexpected Session State [CAI-058]</h1><p>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support.</p>', 400);
		// }

		// $Contact = array_merge($Contact, $chk);
		return $Contact;
	}
}
