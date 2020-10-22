<?php
/**
 * Generate an oAuth2 Token
 */

namespace App\Controller\oAuth2;

class Token extends \OpenTHC\Controller\Base
{
	private $_cfg;
	private $_auth_token;

	function __invoke($REQ, $RES, $ARG)
	{
		$this->_cfg = \OpenTHC\Config::get('openthc/sso');

		$RES = $this->verifyRequest($REQ, $RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		$RES = $this->_load_auth_code($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Make the Session Token
		$data = json_encode(array(
			'client_id' => $this->_auth_token['client_id'],
			'grant_type' => $_POST['grant_type'],
			'contact_id' => $this->_auth_token['contact_id'],
			'scope' => $this->_auth_token['scope'],
		));

		$hash = _random_hash();

		$sql = 'INSERT INTO auth_context_ticket (id, meta) VALUES (?, ?)';
		$arg = array($hash, $data);
		$this->_container->DBC_AUTH->query($sql, $arg);

		// Generate Data Response
		$ret = [];
		$ret['access_token'] = $hash;
		$ret['refresh_token'] = $hex;
		$ret['token_type'] = 'bearer';
		$ret['scope'] = $this->_auth_token['scope'];
		$ret['expires_in'] = 86400 / 2;

		return $RES->withJSON($ret);

	}

	/*
	 * Verify the Request is Good
	 */
	function verifyRequest($REQ, $RES)
	{
		if (empty($_POST['client_id'])) {
			return $this->makeError($RES, 'invalid_client', 'Invalid Client [COT#068]', 401);
		}

		$Program = $this->_container->DB->fetchRow('SELECT id,name,code,hash FROM auth_program WHERE code = ?', array($_POST['client_id']));
		if (empty($Program['id'])) {
			return $this->makeError($RES, 'invalid_client', 'Invalid Client [COT#073]', 401);
		}

		if (empty($_POST['client_secret'])) {
			return $this->makeError($RES, 'invalid_client', 'Invalid Client Secret [COT#077]', 401);
		}

		if ($Program['hash'] != $_POST['client_secret']) {
			return $this->makeError($RES, 'invalid_client', 'Invalid Client Secret [COT#081]', 401);
		}

		if (empty($_POST['grant_type'])) {
			return $this->makeError($RES, 'invalid_grant', 'Invalid Grant Type [COT#085]', 400);
		}

		if ('authorization_code' != $_POST['grant_type']) {
			return $this->makeError($RES, 'unsupported_grant_type', 'Invalid Grant Type [COT#089]', 400);
		}

		if (empty($_POST['code'])) {
			return $this->makeError($RES, 'invalid_request','Invalid Code [COT#093]', 400);
		}

		return $RES;
	}

	/**
	 * Load and Validate the Auth Code
	 */
	function _load_auth_code($RES)
	{
		$dbc_auth = $this->_container->DBC_AUTH;

		$sql = 'SELECT * FROM auth_context_ticket WHERE id = ?';
		$arg = array($_POST['code']);
		$res = $dbc_auth->fetchRow($sql, $arg);

		// And Delete it
		$sql = 'DELETE FROM auth_context_ticket WHERE id = ?';
		$arg = array($_POST['code']);
		$dbc_auth->query($sql, $arg);

		if (empty($res)) {
			return $this->makeError($RES, 'access_denied', 'Invalid Code [COT#113]', 401);
		}
		$tok = json_decode($res['meta'], true);

		if (empty($tok['contact_id'])) {
			return $this->makeError($RES, 'access_denied', 'Invalid Token Data [COT#118]', 401);
		}

		if ($tok['client_id'] != $_POST['client_id']) {
			return $this->makeError('access_denied', 'Invalid Token Data [COT#122]', 401);
		}

		if (empty($tok['scope'])) {
			return $this->makeError($RES, 'invalid_request', 'Invalid Scope [COT#126]', 400);
		}

		$this->_auth_token = $tok;

		return $RES;
	}

	/**
	 * Error Response Helper
	 * @param object $RES [description]
	 * @param string $e [description]
	 * @param string $d [description]
	 * @param integer $http_code [description]
	 * @return object $RES
	 */
	function makeError($RES, $e, $d, $http_code=400)
	{
		return $RES->withJSON(array(
			'error' => $e,
			'error_description' => $d,
			'error_uri' => sprintf('%s/doc/oauth2', $this->_cfg['url']),
			'state' => $_POST['state']
		), $http_code);
	}

}
