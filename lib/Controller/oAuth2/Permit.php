<?php
/**
 * Contact Permited Authorization
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\oAuth2;

class Permit extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		if (empty($_GET['_'])) {
			__exit_text('Invalid Input [COP-013]', 400);
		}

		$x = _decrypt($_GET['_'], $_SESSION['crypt-key']);
		$x = json_decode($x, true);
		if (empty($x)) {
			__exit_text('Invalid Input [COP-019]', 400);
		}

		$_GET = $x;

		// Load & Validate The Client
		$sql = 'SELECT id, name, code, hash, context_list FROM auth_service WHERE (id = :c0 OR code = :c0)';
		$arg = [
			':c0' => $_GET['client_id'],
		];
		$Auth_Service = $this->_container->DBC_AUTH->fetchRow($sql, $arg);
		if (empty($Auth_Service['id'])) {
			_exit_json(array(
				'error' => 'invalid_client',
				'error_description' => 'Invalid Client [COA-061]',
				'error_uri' => sprintf('%s/auth/doc', OPENTHC_SERVICE_ORIGIN),
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
			$ruri['query'] = __parse_str($ruri['query']);
		}

		$ruri['query']['code'] = $hash;
		$ruri['query']['state'] = $_GET['state'];
		ksort($ruri['query']);

		$ruri['query'] = http_build_query($ruri['query']);

		$ret = _url_assemble($ruri);

		// Configured to hide Confirm Prompt
		if (\OpenTHC\Config::get('sso/redirect-fast')) {
			return $RES->withRedirect($ret);
		}

		$data = [];
		$data['Page'] = ['title' => 'Permit' ];
		$data['Contact'] = $_SESSION['Contact'];
		$data['Company'] = $_SESSION['Company'];
		$data['Service'] = $Auth_Service;
		$data['return_url'] = $ret;

		return $RES->write( $this->render('oauth2/permit.php', $data) );

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
