<?php
/**
 * Initialise an Authenticated Session
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Auth;

use Edoceo\Radix\Session;

use OpenTHC\Contact;

class Init extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		// Check Input
		if (!preg_match('/^([\w\-]{32,128})$/i', $_GET['_'], $m)) {
			_exit_html_warn('<h1>Invalid Request [CAI-026]</h1>', 400);
		}

		// Load Auth Ticket
		$act_data = \OpenTHC\SSO\Auth_Context_Ticket::get($_GET['_']);
		if (empty($act_data['contact']['id'])) {
			_exit_html_warn('<h1>Invalid Request [CAI-038]</h1>', 400);
		}

		// Clear Session
		$_SESSION = [];

		// Load Location
		$this->loadGeoIP();

		$dbc_auth = $this->_container->DBC_AUTH;

		// Check Intent
		switch ($act_data['intent']) {
			case 'account-create':
			case 'account-open':
			case 'oauth-authorize':
				// OK
				break;
			default:
				_exit_html_warn('<h1>Invalid Request [CAI-046]</h1>', 400);
				break;
		}

		// Contact has Disabled Flags?
		$Contact = $this->contact_inflate($act_data['contact']);

		// Contact Status Switch
		switch ($Contact['stat']) {
			case Contact::STAT_INIT:
				$jwt = new \OpenTHC\JWT([
					'service' => 'www',
					'r' => '/verify/done',
				]);
				$cfg_www = \OpenTHC\Config::get('openthc/www');
				$r = sprintf("https://%s/auth/jwt?jwt=%s", $cfg_www['hostname'], $jwt->__toString());
				return $RES->withRedirect($r);

				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
				break;
			case Contact::STAT_LIVE:
				// OK
				return $this->account_init($RES, $act_data, $Contact);
				break;
			case 410:
				_exit_html_warn('<h1>Invalid Account [CAI-049]</h1>', 403);
				break;
		}

		_exit_html_warn('<h1>Invalid Request [CAI-066]</h1>', 400);

	}

	/**
	 * Load GeoIP Data to Session
	 */
	protected function loadGeoIP() : void
	{
		// Would like to put this behind a cache
		if ( ! empty($_SESSION['geoip'])) {
			return;
		}

		$cfg = \OpenTHC\Config::get('maxmind');

		if (empty($cfg['account'])) {
			return;
		}

		$api = new \GeoIp2\WebService\Client($cfg['account'], $cfg['license-key']);
		$geo = $api->city($_SERVER['REMOTE_ADDR']);
		$raw = $geo->raw;

		$_SESSION['geoip'] = true;

		$_SESSION['iso3166_1'] = [
			'id' => $raw['country']['iso_code'],
			'name' => $raw['country']['names']['en'],
		];

		$_SESSION['iso3166_2'] = [
			'id' => sprintf('%s-%s', $raw['country']['iso_code'], $raw['subdivisions'][0]['iso_code']),
			'name' => $raw['subdivisions'][0]['names']['en']
		];

		$_SESSION['tz'] = $raw['location']['time_zone'];

	}

	/**
	 *
	 */
	protected function account_init($RES, $act_data, $Contact)
	{
		$dbc_auth = $this->_container->DBC_AUTH;

		/**
		 * Initialize Company Data in $act_data & return
		 */
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
  AND auth_company_contact.stat IN (100, 200)
ORDER BY auth_company_contact.stat, auth_company.name ASC
SQL;

		$arg = [ ':c0' => $Contact['id'] ];
		$company_list = $dbc_auth->fetchAll($sql, $arg);

		// Company/Contact Link
		switch (count($company_list)) {
			case 0:
				// return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
				_exit_html_fail('<h1>Unexpected Session State [CAI-051]</h1><p>You may want to <a href="/auth/shut">close your session</a> and try again.</p><p>If the issue continues, contact support</p>', 500);
				break;
			case 1:
				$Company = $company_list[0];
				return $this->_create_ticket_and_redirect($RES, $act_data, $Contact, $Company);
				break;
			default:

				// User with Many Company Links AND they picked one
				if (!empty($_POST['company_id'])) {
					foreach ($company_list as $c) {
						if ($c['id'] === $_POST['company_id']) {
							$Company = $c;
							return $this->_create_ticket_and_redirect($RES, $act_data, $Contact, $Company);
							break;
						}
					}
				}

				$data = $this->data;
				$data['Page']['title'] = 'Select Company';
				$data['company_list'] = $company_list;

				$RES = $RES->write( $this->render('auth/init.php', $data) );
				return $RES->withStatus(300);

		}

		return $RES->withJSON([
			'data' => null,
			'meta' => [ 'detail' => 'Unexpected Server Error [CAI-108] '],
		], 500);

	}

	/**
	 * @return \OpenTHC\SSO\Response ready to be redirected
	 */
	protected function _create_ticket_and_redirect($RES, $act_data, $Contact, $Company) : \OpenTHC\SSO\Response
	{
		$_SESSION['Contact'] = $Contact;
		$_SESSION['Company'] = $Company;

		$act_data['company'] = $Company;

		$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act_data);

		// No Return? Load Default
		$ret = '/account';
		switch ($act_data['intent']) {
			case 'account-create':
			case 'account-open':

				// Requested Service ? DEFAULT
				// if (empty($act_data['service'])) {
				// 	$cfg = \OpenTHC\Config::get('openthc/app/base');
				// 	if (!empty($cfg)) {
				// 		$act_data['service'] = $cfg;
				// 	}
				// }

				if ( ! empty($act_data['service'])) {
					// @todo Lookup Service in Database before building this link?
					// So it's only going against known services
					$ret = sprintf('https://%s/auth/back?ping={PING}', $act_data['service']);
				}

				// Place Ping Back Token
				$ping = sprintf('%s/auth/once?_=%s', OPENTHC_SERVICE_ORIGIN, $tok);
				$ret = str_replace('{PING}', $ping, $ret);

				break;

			case 'oauth-authorize':
				$ret = '/oauth2/authorize?' . http_build_query($act_data['oauth-request']);
				break;
			default:
				_exit_html_warn('<h1>Invalid Request [CAI-188]</h1>', 400);
		}

		return $RES->withRedirect($ret);

	}

	/**
	 * Inflate Contact from Auth & Main
	 */
	protected function contact_inflate($Contact)
	{
		// Auth/Contact
		$sql = 'SELECT id, username, password, stat, flag, iso3166, tz FROM auth_contact WHERE id = :pk';
		$arg = [ ':pk' => $Contact['id'] ];
		$CT0 = $this->_container->DBC_AUTH->fetchRow($sql, $arg);
		if (empty($CT0['id'])) {
			_exit_html_fail('<h1>Unexpected Session State [CAI-047]</h1><p>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support.</p>', 500);
		}

		// Base/Contact
		$sql = 'SELECT id, name AS fullname, phone, email FROM contact WHERE id = :pk';
		$arg = [ ':pk' => $Contact['id'] ];
		$CT1 = $this->_container->DBC_MAIN->fetchRow($sql, $arg);
		if (empty($CT1['id'])) {
			_exit_html_fail('<h1>Unexpected Session State [CAI-058]</h1><p>You should <a href="/auth/shut">close your session</a> and try again<br>If the issue continues, contact support.</p>', 500);
		}

		$Contact = array_merge($CT0, $CT1);

		return $Contact;
	}
}
