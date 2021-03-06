<?php
/**
 *
 */


namespace App\Controller\Verify;

class Base extends \App\Controller\Base
{
	function loadTicket()
	{
		$dbc_auth = $this->_container->DBC_AUTH;
		$chk = $dbc_auth->fetchRow('SELECT expires_at, meta FROM auth_context_ticket WHERE id = :t', [ ':t' => $_GET['_']]);
		if (empty($chk['meta'])) {
			$dbc_auth->query('DELETE FROM auth_context_ticket WHERE id = :t0', [ ':t0' => $_GET['_'] ]);
			return $RES->withRedirect('/done?' . http_build_query([
				'_' => $_GET['_'],
				'e' => 'cao066'
			]));
		}
		$act = json_decode($chk['meta'], true);

		if (empty($act)) {
			__exit_text('Invalid Request [CVB-024]', 400);
		}

		// Load Contact (from ticket, no DB?)
		$sql = <<<SQL
SELECT auth_contact.id, auth_contact.stat, auth_contact.flag, auth_contact.username
FROM auth_contact
WHERE auth_contact.id = :c0
SQL;
		$arg = [
			':c0' => $act['contact']['id']
		];

		// Inflate this onto the ACT
		$CT0 = $dbc_auth->fetchRow($sql, $arg);
		if (empty($CT0['id'])) {
			_err_exit_html('Invalid Request [CAV-037]', 400);
		}

		$CT1 = $this->_container->DBC_MAIN->fetchRow('SELECT id, email, phone FROM contact WHERE id = :c0', $arg);
		if (empty($CT1['id'])) {
			_err_exit_html('Invalid Request [CAV-040]', 400);
		}

		$act['contact']['flag'] = $CT0['flag'];
		$act['contact']['stat'] = $CT0['stat'];
		$act['contact']['email'] = $CT0['username'];
		$act['contact']['phone'] = $CT1['phone'];

		return $act;

	}

}
