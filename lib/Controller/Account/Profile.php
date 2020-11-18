<?php
/**
 * View your Own Account
 */

namespace App\Controller\Account;

class Profile extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$file = 'page/account/profile.html';

		$data = $this->data;
		$data['Page'] = [ 'title' => 'Account' ];

		$dbc_auth = $this->_container->DBC_AUTH;
		$dbc_main = $this->_container->DBC_MAIN;

		// $dbc_auth->query('BEGIN');
		// $dbc_main->query('BEGIN');
		$C0 = $dbc_auth->fetchRow('SELECT * FROM auth_contact WHERE id = :ct0', [ ':ct0' => $_SESSION['Contact']['id'] ]);
		$C1 = $dbc_main->fetchRow('SELECT * FROM contact WHERE id = :ct0', [ ':ct0' => $_SESSION['Contact']['id'] ]);

		$data['Contact_Auth'] = $C0;
		$data['Contact_Base'] = $C1;

		// Company List
		$res = $dbc_auth->fetchAll('SELECT id, name FROM auth_company WHERE id IN (SELECT company_id FROM auth_company_contact WHERE contact_id = :ct0)', [
			':ct0' => $_SESSION['Contact']['id'],
		]);
		$data['company_list'] = $res;

		return $this->_container->view->render($RES, $file, $data);

	}
}
