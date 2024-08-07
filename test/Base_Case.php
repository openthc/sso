<?php
/**
 * Base Class for API Testing
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test;

class Base_Case extends \OpenTHC\Test\Base // \PHPUnit\Framework\TestCase
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
			'Domain' => parse_url(OPENTHC_TEST_ORIGIN, PHP_URL_HOST), // $this->httpClient->getConfig('base_uri')->getHost(),
			'Name' => 'openthc-test',
			'Value' => \OpenTHC\Config::get('openthc/sso/test/sk'),
			'Secure' => true,
			'HttpOnly' => true,
		]);
		$jar->setCookie($obj);


		if (empty($c)) {

			$c = new \GuzzleHttp\Client(array(
				'base_uri' => OPENTHC_TEST_ORIGIN,
				'allow_redirects' => false,
				'debug' => DEBUG_HTTP,
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
	 *
	 */
	function assertValidResponse($res, $code=200, $type_expect='text/html', $dump=null)
	{
		$this->raw = $res->getBody()->getContents();

		$hrc = $res->getStatusCode();

		if (empty($dump)) {
			if ($code != $hrc) {
				$dump = "HTTP $hrc != $code";
			}
		}

		if (!empty($dump)) {
			echo "\n<<< $dump <<< $hrc <<<\n{$this->raw}\n###\n";
		}

		$this->assertEquals($code, $res->getStatusCode());
		$type_actual = $res->getHeaderLine('content-type');
		$type_actual = strtok($type_actual, ';');
		$this->assertEquals($type_expect, $type_actual);

		return $this->raw;

	}

	/**
	 * Parses the CSRF
	 */
	function getCSRF(string $html)
	{
		return (preg_match('/name="CSRF" type="hidden" value="([^"]+)"/', $html, $m) ? $m[1] : 'CSRF');
	}

}
