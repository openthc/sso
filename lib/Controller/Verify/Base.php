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
SELECT auth_contact.id, auth_contact.flag, auth_contact.username
FROM auth_contact
WHERE auth_contact.id = :c0
SQL;
		$arg = [
			':c0' => $act['contact']['id']
		];

		// $Contact = $this->_container->DBC_AUTH->fetchRow($sql, $arg);
		// if (empty($Contact['id'])) {
		// 	_err_exit_html('Invalid Request [CAV-037]', 400);
		// }
		// $Contact_Base = $this->_container->DBC_MAIN->fetchRow('SELECT id, email, phone FROM contact WHERE id = :c0', $arg);
		// if (empty($Contact_Base['id'])) {
		// 	_err_exit_html('Invalid Request [CAV-040]', 400);
		// }


		return $act;

	}

}
