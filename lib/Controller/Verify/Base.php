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

		return $act;

	}

}
