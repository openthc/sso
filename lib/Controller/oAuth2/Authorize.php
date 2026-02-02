<?php
/**
 * oAuth2 Authorize
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\oAuth2;

class Authorize extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$this->verifyRequest();

		$dbc_auth = $this->dic->get('DBC_AUTH');

		// Good Session?
		$RES = $this->verifySession($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Validate Service
		$sql = 'SELECT id, name, code, hash, context_list FROM auth_service WHERE (id = :c0 OR code = :c0)';
		$arg = [ ':c0' => $_GET['client_id'] ];
		$Auth_Service = $dbc_auth->fetchRow($sql, $arg);
		if (empty($Auth_Service['id'])) {
			_exit_json(array(
				'error' => 'invalid_client',
				'error_description' => 'Invalid Client [COA-051]',
				'error_uri' => sprintf('%s/auth/doc', OPENTHC_SERVICE_ORIGIN),
			), 401);
		}

		$scope_want = $this->verifyScope();

		$this->verifyScopeAccess($Auth_Service, $scope_want);


		// Permit Link
		$link_crypt = _encrypt(json_encode($_GET), $_SESSION['crypt-key']);

		// Did you already Authorize this Application?
		$sql = 'SELECT count(service_id) FROM auth_service_contact WHERE service_id = ? AND contact_id = ? AND expires_at > now()';
		$arg = array($Auth_Service['id'], $_SESSION['Contact']['id']);
		$chk = $dbc_auth->fetchOne($sql, $arg);
		if ( ! empty($chk)) {
			return $this->redirect('/oauth2/permit?a=fast&_=' . $link_crypt);
		}

		// Permit & Remember
		$_GET['auth-commit'] = true;
		$link_crypt_save = _encrypt(json_encode($_GET), $_SESSION['crypt-key']);

		// Always push through Authorize UX
		return $this->redirect('/oauth2/permit?_=' . $link_crypt);

		$data = [];
		$data['Page'] = [ 'title' => 'Authorize' ];
		$data['Contact'] = $_SESSION['Contact'];
		$data['Company'] = $_SESSION['Company'];
		$data['Service'] = $Auth_Service;
		$data['scope_list'] = explode(' ', $_GET['scope']);
		$data['link_crypt'] = $link_crypt;
		$data['link_crypt_save'] = $link_crypt_save;

		return $RES->getBody()->write( $this->render('oauth2/authorize.php', $data) );

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

		// Only if Require SSL
		$chk = \OpenTHC\Config::get('openthc/sso/require-ssl');
		if ($chk) {
			if ('https' != $ruri['scheme']) {
				__exit_text('Invalid Redirect Scheme [COA-040]', 400);
			}
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
	 * @return array
	 */
	function verifyScope() : array
	{
		$dbc_auth = $this->dic->get('DBC_AUTH');

		$res = $dbc_auth->fetchAll('SELECT code FROM auth_context');
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
	function verifyScopeAccess($Service, $scope_ask) : array
	{
		$scope_may = explode(' ', $Service['context_list']);
		$scope_ret = [];

		foreach ($scope_ask as $s) {
			if (!in_array($s, $scope_may, true)) {
				$html = sprintf('<h1>Access Denied to Context &quot;%s&quot; [COA-151]</h1>', $s);
				$html.= '<p>See <a href="https://openthc.org/err#sso/COA-151">documentation</a></p>';
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

			$tok = \OpenTHC\SSO\Auth_Context_Ticket::set([
				'intent' => 'oauth-authorize',
				'contact' => [],
				'company' => [],
				'service' => $_GET['client_id'],
				'oauth-request' => $_GET,
			]);

			$ret = sprintf('/auth/open?_=%s', $tok);

			return $this->redirect($ret);

		}

		// If no Company, Re-Open and Select Company
		if (empty($_SESSION['Company']['id'])) {

			$tok = \OpenTHC\SSO\Auth_Context_Ticket::set([
				'intent' => 'oauth-authorize',
				'contact' => $_SESSION['Contact'],
				'company' => [],
				'service' => $_GET['client_id'],
				'oauth-request' => $_GET,
			]);

			$ret = sprintf('/auth/open?_=%s', $tok);

			return $this->redirect($ret);
		}

		return $RES;

	}
}
