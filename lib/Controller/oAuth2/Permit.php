<?php
/**
 * Contact Permited Authorization
 */

namespace App\Controller\oAuth2;

class Permit extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		if (empty($_GET['_'])) {
			__exit_text('Invalid Input [COP#013]', 400);
		}

		$x = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$x = json_decode($x, true);
		if (empty($x)) {
			__exit_text('Invalid Input [COP#019]', 400);
		}

		$_GET = $x;

		$cfg = \OpenTHC\Config::get('oauth');
		$_ENV['fast-redirect'] = $cfg['fast-redirect'];

		// Load & Validate The Client
		$Auth_Service = $this->_container->DBC_AUTH->fetchRow('SELECT id,name,code,hash FROM auth_service WHERE code = ?', array($_GET['client_id']));
		if (empty($Auth_Service['id'])) {
			_exit_json(array(
				'error' => 'invalid_client',
				'error_description' => 'COA#061: Invalid Client',
				'error_uri' => 'https://openthc.com/auth/doc',
			), 401);
		}

		// Save the Authorization Code for the remote-application to callback with
		$data = json_encode(array(
			'client_id' => $_GET['client_id'],
			'contact_id' => $_SESSION['Contact']['id'],
			'company_id' => $_SESSION['Company']['id'],
			'scope' => $_GET['scope'],
		));

		$hash = _random_hash();

		$sql = 'INSERT INTO auth_context_ticket (id, meta, expires_at) VALUES (?, ?, ?)';
		$arg = array(
			$hash,
			$data,
			strftime('%Y-%m-%d %H:%M:%S', $_SERVER['REQUEST_TIME'] + 300),
		);
		$this->_container->DBC_AUTH->query($sql, $arg);

		$this->_permit_and_save($Auth_Service);

		// Rebuild URL (it's checked before input to this page)
		$ruri = parse_url($_GET['redirect_uri']);
		if (empty($ruri['query'])) {
			$ruri['query'] = array();
		} elseif (!empty($ruri['query'])) {
			$ruri['query'] = _parse_str($ruri['query']);
		}

		$ruri['query']['code'] = $hash;
		$ruri['query']['state'] = $_GET['state'];
		ksort($ruri['query']);

		$ruri['query'] = http_build_query($ruri['query']);

		$ret = _url_assemble($ruri);

		if ($_ENV['fast-redirect']) {
			return $RES->withRedirect($ret);
		}

		$data = [];
		$data['Page'] = ['title' => 'Permit' ];
		$data['Contact'] = $_SESSION['Contact'];
		$data['Company'] = $_SESSION['Company'];
		$data['Service'] = $Auth_Service;
		$data['return_url'] = $ret;

		$file = 'page/oauth2/permit.html';

		return $this->_container->view->render($RES, $file, $data);

	}

	/**
	 * Save Permit Commit
	 */
	function _permit_and_save($Auth_Service)
	{
		// Remember this application authorization
		if (!empty($_GET['auth-commit'])) {
			$sql = 'INSERT INTO auth_service_contact (service_id, contact_id) VALUES (:a, :u)';
			$arg = array(
				':a' => $Auth_Service['id'],
				':u' => $_SESSION['Contact']['id'],
				// 'expires_at'
			);
			$res = $this->_container->DBC_AUTH->query($sql, $arg);
		}
	}
}
