<?php
/**
 * Base Class for API Testing
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test;

class Base extends \OpenTHC\Test\Base
{
	protected $_pid = null;

	protected $raw = '';

	/**
	 *
	 */
	function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->_pid = getmypid();

	}

	/**
	 * Gets a User-Agent from Guzzle
	 */
	protected function _ua()
	{
		static $c;

		// Set Cookies
		$jar = new \GuzzleHttp\Cookie\CookieJar();
		$obj = new \GuzzleHttp\Cookie\SetCookie([
			'Domain' => parse_url($_ENV['OPENTHC_TEST_ORIGIN'], PHP_URL_HOST),
			'Name' => 'openthc-test',
			'Value' => \OpenTHC\Config::get('openthc/sso/test/sk'),
			'Secure' => true,
			'HttpOnly' => true,
		]);
		$jar->setCookie($obj);

		if (empty($c)) {

			$c = new \GuzzleHttp\Client(array(
				'base_uri' => $_ENV['OPENTHC_TEST_ORIGIN'],
				'allow_redirects' => false,
				'debug' => $_ENV['OPENTHC_TEST_HTTP_DEBUG'],
				'request.options' => array(
					'exceptions' => false,
				),
				'http_errors' => false,
				'cookies' => $jar,
			));
		}

		return $c;

	}

	protected function _dbc()
	{
		static $dbc;
		if (empty($dbc)) {
			$cfg = \OpenTHC\Config::get('database/auth');
			$dsn = sprintf('pgsql:application_name=openthc-sso;host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
			return new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);
		}
	}

	/**
	 * Parses the CSRF
	 */
	function getCSRF(string $html)
	{
		return (preg_match('/name="CSRF" type="hidden" value="([^"]+)"/', $html, $m) ? $m[1] : 'CSRF');
	}

}
