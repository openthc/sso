<?php
/**
 * oAuth2 Authorize
 */

namespace App\Controller\oAuth2;

class Authorize extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$this->verifyRequest();

		// Validate Client
		$Auth_Program = $this->_container->DB->fetchRow('SELECT id,name,code,hash FROM auth_program WHERE code = ?', array($_GET['client_id']));
		if (empty($Auth_Program['id'])) {
			_exit_json(array(
				'error' => 'invalid_client',
				'error_description' => 'Invalid Client [COA#051]',
				'error_uri' => sprintf('https://%s/auth/doc', $_SERVER['SERVER_NAME']),
			), 401);
		}

		$this->verifyScope();

		$RES = $this->verifySession($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

	}

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
				_exit_text("Missing Parameter '$k' [COA#025]", 400);
			}
		}

		// Validate Response Type
		if ('code' != $_GET['response_type']) {
			_exit_text('Invalid Response Type [VOA#031]', 400);
		}

		// Validate Redirect URI
		$ruri = parse_url($_GET['redirect_uri']);
		if (empty($ruri['scheme'])) {
			_exit_text('Missing Redirect Scheme [COA#037]', 400);
		}
		if ('https' != $ruri['scheme']) {
			_exit_text('Invalid Redirect Scheme [COA#040]', 400);
		}

		if (empty($ruri['host'])) {
			_exit_text('Missing Redirect Host [COA#043]', 400);
		}
		// @todo Filter Invalid Host Names

		if (empty($ruri['path'])) {
			_exit_text('Missing Redirect Path [COA#046]', 400);
		}

	}

	function verifyScope()
	{
		$scope_list_may = [
			'aux',
			'profile',
			'crm',
			'dump',
			'lab',
			'menu',
			'ops',
			'p2p',
			'pos',
		];

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

		foreach ($scope_list_ask as $s) {
			if (!in_array($s, $scope_list_may)) {
				_exit_text("Unknown Scope '$s' [COA#088]", 400);
			}
		}

	}

	function verifySession($RES)
	{
		// If no session then sign-in and come back here
		if (empty($_SESSION['uid'])) {

			$ret = sprintf('https://%s/auth/open?', $_SERVER['SERVER_NAME']);
			$ret.= http_build_query([
				'a' => 'oauth',
				'r' => sprintf('https://%s/oauth2/authorize?%s', $_SERVER['SERVER_NAME'], http_build_query($_GET)),
			]);

			return $RES->withRedirect($ret);

		}

		return $RES;

	}
}
