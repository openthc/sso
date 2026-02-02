<?php
/**
 * Base Controller for Verify
 *
 * SPDX-License-Identifier: MIT
 */


namespace OpenTHC\SSO\Controller\Verify;

class Base extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function loadTicket() : ?array
	{
		// Load Auth Ticket or DIE
		$act = \OpenTHC\SSO\Auth_Context_Ticket::get($_GET['_']);
		if (empty($act)) {
			_exit_html_warn('<h1>Invalid Request [CVB-026]</a></h1><p>You will need to <a href="/auth/open">Sign In Again</a></p>', 400);
		}

		if (empty($act['contact_cache'])) {

			$dbc_auth = $this->dic->get('DBC_AUTH');
			$dbc_main = $this->dic->get('DBC_MAIN');

			// Load Contact
			$sql = <<<SQL
			SELECT auth_contact.id
			, auth_contact.stat
			, auth_contact.flag
			, auth_contact.username
			, auth_contact.password
			, auth_contact.iso3166
			, auth_contact.tz
			FROM auth_contact
			WHERE auth_contact.id = :c0
			SQL;
			$arg = [
				':c0' => $act['contact']['id']
			];

			// Inflate this onto the ACT
			$CT0 = $dbc_auth->fetchRow($sql, $arg);
			if (empty($CT0['id'])) {
				throw new \Exception('Invalid Request [CAV-037]', 400);
			}

			$CT1 = $dbc_main->fetchRow('SELECT id, email, phone FROM contact WHERE id = :c0', $arg);
			if (empty($CT1['id'])) {
				_exit_html_fail('<h1>Invalid Request [CAV-040]</h1>', 400);
			}

			$act['contact']['flag'] = $CT0['flag'];
			$act['contact']['stat'] = $CT0['stat'];
			$act['contact']['username'] = $CT0['username'];
			$act['contact']['password'] = $CT0['password'];
			$act['contact']['iso3166'] = $CT0['iso3166'];
			$act['contact']['tz'] = $CT0['tz'];

			$act['contact']['email'] = $CT1['email'];
			$act['contact']['phone'] = $CT1['phone'];

		}

		return $act;

	}

}
