<?php
/**
 * oAuth2 Authorize
 */

namespace App\Controller\oAuth2;

class Authorize extends \App\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$this->verifyRequest();

		$dbc = $this->_container->DBC_AUTH;

		// Good Session?
		$RES = $this->verifySession($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Validate Service
		$Auth_Service = $dbc->fetchRow('SELECT id,name,code,hash,context_list FROM auth_service WHERE code = ?', array($_GET['client_id']));
		if (empty($Auth_Service['id'])) {
			_exit_json(array(
				'error' => 'invalid_client',
				'error_description' => 'Invalid Client [COA-051]',
				'error_uri' => sprintf('https://%s/auth/doc', $_SERVER['SERVER_NAME']),
			), 401);
		}

		$scope_want = $this->verifyScope();

		$this->verifyScopeAccess($Auth_Service, $scope_want);


		// Permit Link
		$link_crypt = _encrypt(json_encode($_GET), $_SESSION['crypt-key']);

		// Did you already Authorize this Application?
		$sql = 'SELECT count(service_id) FROM auth_service_contact WHERE service_id = ? AND contact_id = ? AND expires_at > now()';
		$arg = array($Auth_Service['id'], $_SESSION['Contact']['id']);
		$chk = $dbc->fetchOne($sql, $arg);
		if (!empty($chk)) {
			return $RES->withRedirect('/oauth2/permit?a=fast&_=' . $link_crypt);
		}

		// Permit & Remember
		$_GET['auth-commit'] = true;
		$link_crypt_save = _encrypt(json_encode($_GET), $_SESSION['crypt-key']);

		$data = [];
		$data['Page'] = [ 'title' => 'Authorize' ];
		$data['Contact'] = $_SESSION['Contact'];
		$data['Company'] = $_SESSION['Company'];
		$data['Service'] = $Auth_Service;
		$data['scope_list'] = explode(' ', $_GET['scope']);
		$data['link_crypt'] = $link_crypt;
		$data['link_crypt_save'] = $link_crypt_save;

		return $RES->write( $this->render('oauth2/authorize.php', $data) );

	}

	/**
	 *
	 */
	function verifyRequest()
	{
		// Validate Inputs
		$key_list = array(
			'client_id',
			'redirect_uri',
			'response_type',
			'scope',
			'state'
		);
		foreach ($key_list as $k) {
			$_GET[$k] = trim($_GET[$k]);
			if (empty($_GET[$k])) {
				__exit_text("Missing Parameter '$k' [COA-025]", 400);
			}
		}

		// Validate Response Type
		if ('code' != $_GET['response_type']) {
			__exit_text('Invalid Response Type [COA-031]', 400);
		}

		// Validate Redirect URI
		$ruri = parse_url($_GET['redirect_uri']);
		if (empty($ruri['scheme'])) {
			__exit_text('Missing Redirect Scheme [COA-037]', 400);
		}
		if ('https' != $ruri['scheme']) {
			__exit_text('Invalid Redirect Scheme [COA-040]', 400);
		}

		if (empty($ruri['host'])) {
			__exit_text('Missing Redirect Host [COA-043]', 400);
		}
		// @todo Filter Invalid Host Names

		if (empty($ruri['path'])) {
			__exit_text('Missing Redirect Path [COA-046]', 400);
		}

	}

	/**
	 * [verifyScope description]
	 * Modifies the Scope in $_GET
	 * @return void
	 */
	function verifyScope()
	{
		$res = $this->_container->DBC_AUTH->fetchAll('SELECT code FROM auth_context');
		$scope_list_all = array_reduce($res, function($ret, $cur) {
			$ret[] = $cur['code'];
			return $ret;
		}, []);

		// Scope List being asked for by Client
		$scope_list_ask = array();
		$scope_list_tmp = explode(' ', $_GET['scope']);
		foreach ($scope_list_tmp as $x) {
			$x = trim($x);
			if (empty($x)) {
				continue;
			}
			$scope_list_ask[] = $x;
		}
		$scope_list_ask = array_unique($scope_list_ask);
		sort($scope_list_ask);

		foreach ($scope_list_ask as $s) {
			if (!in_array($s, $scope_list_all)) {
				__exit_text("Unknown Scope '$s' [COA-088]", 400);
			}
		}

		$_GET['scope'] = implode(' ', $scope_list_ask);

		return $scope_list_ask;

	}

	/**
	 *
	 */
	function verifyScopeAccess($Service, $scope_ask)
	{
		$scope_may = explode(' ', $Service['context_list']);

		foreach ($scope_ask as $s) {
			if (!in_array($s, $scope_may, true)) {
				$html = sprintf('<h1>Access Denied to Context &quot;%s&quot; [COA-151]</h1>', $s);
				$html.= sprintf('<p>See <a href="https://%s/doc#COA-151">documentation</p>', $_SERVER['SERVER_NAME']);
				$html.= '<p>Or <a href="/auth/shut">sign-out</a> and start over</p>';
				__exit_html($html, 403);
			}
			$scope_ret[] = $s;
		}

		return $scope_ret;

	}

	/**
	 * Verify Session or Request Redirect
	 * @return Response maybe with status != 200
	 */
	function verifySession($RES)
	{
		// If no Contact, Request Sign-In
		if (empty($_SESSION['Contact']['id'])) {

			$act = [];
			$act['id'] = _random_hash();
			$act['meta'] = json_encode([
				'intent' => 'oauth-authorize',
				'contact' => [],
				'company' => [],
				'service' => $_GET['client_id'],
				'oauth-request' => $_GET,
			]);

			$this->_container->DBC_AUTH->insert('auth_context_ticket', $act);
			$ret = sprintf('https://%s/auth/open?_=%s', $_SERVER['SERVER_NAME'], $act['id']);

			return $RES->withRedirect($ret);

		}

		// If no Company, Re-Open and Select Company
		if (empty($_SESSION['Company']['id'])) {

			$act = [];
			$act['id'] = _random_hash();
			$act['meta'] = json_encode([
				'intent' => 'oauth-authorize',
				'contact' => $_SESSION['Contact'],
				'company' => [],
				'service' => $_GET['client_id'],
				'oauth-request' => $_GET,
			]);

			$this->_container->DBC_AUTH->insert('auth_context_ticket', $act);
			$ret = sprintf('https://%s/auth/open?_=%s', $_SERVER['SERVER_NAME'], $act['id']);

			return $RES->withRedirect($ret);
		}

		return $RES;

	}
}
