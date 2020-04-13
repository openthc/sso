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

		$dbc = $this->_container->DB;

		// Good Session?
		$RES = $this->verifySession($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Validate Program
		$Auth_Program = $dbc->fetchRow('SELECT id,name,code,hash,scope_list FROM auth_program WHERE code = ?', array($_GET['client_id']));
		if (empty($Auth_Program['id'])) {
			_exit_json(array(
				'error' => 'invalid_client',
				'error_description' => 'Invalid Client [COA#051]',
				'error_uri' => sprintf('https://%s/auth/doc', $_SERVER['SERVER_NAME']),
			), 401);
		}

		$scope_want = $this->verifyScope();

		$this->verifyScopeAccess($Auth_Program, $scope_want);


		// Permit Link
		$link_crypt = _encrypt(json_encode($_GET), $_SESSION['crypt-key']);

		// Did you already Authorize this Application?
		$sql = 'SELECT count(auth_program_id) FROM auth_program_contact WHERE auth_program_id = ? AND auth_contact_id = ? AND expires_at > now()';
		$arg = array($Auth_Program['id'], $_SESSION['Contact']['id']);
		$chk = $dbc->fetchOne($sql, $arg);
		if (!empty($chk)) {
			// return $RES->withRedirect('/oauth2/permit?_=' . $link_crypt);
		}

		// Permit & Remember
		$_GET['auth-commit'] = true;
		$link_crypt_save = _encrypt(json_encode($_GET), $_SESSION['crypt-key']);

		$data = [];
		$data['Page'] = [ 'title' => 'Authorize' ];
		$data['Program'] = $Auth_Program;
		$data['scope_list'] = $_GET['scope'];
		$data['link_crypt'] = $link_crypt;
		$data['link_crypt_save'] = $link_crypt_save;

		$file = 'page/oauth2/authorize.html';

		return $this->_container->view->render($RES, $file, $data);

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
			_exit_text('Invalid Response Type [COA#031]', 400);
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

	/**
	 * [verifyScope description]
	 * Modifies the Scope in $_GET
	 * @return void
	 */
	function verifyScope()
	{
		$res = $this->_container->DB->fetchAll('SELECT code FROM auth_scope');
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
				_exit_text("Unknown Scope '$s' [COA#088]", 400);
			}
		}

		$_GET['scope'] = implode(' ', $scope_list_ask);

		return $scope_list_ask;

	}

	function verifyScopeAccess($Program, $scope_ask)
	{
		$scope_may = explode(' ', $Program['scope_list']);

		foreach ($scope_ask as $s) {
			if (!in_array($s, $scope_may, true)) {
				_exit_html("Access Denied to Scope '$s' [COA#151]<br><a href='/auth/shut'>sign-out</a>", 403);
			}
			$scope_ret[] = $s;
		}

		return $scope_ret;

	}

	function verifySession($RES)
	{
		// If no session then sign-in and come back here
		if (empty($_SESSION['Contact']['id'])) {

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
