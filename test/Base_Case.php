<?php
/**
 * Base Class for API Testing
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test;

class Base_Case extends \PHPUnit\Framework\TestCase // \OpenTHC\Test\Base_Case
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

		if (empty($c)) {

			$c = new \GuzzleHttp\Client(array(
				'base_uri' => getenv('OPENTHC_TEST_ORIGIN'),
				'allow_redirects' => false,
				'debug' => $_ENV['debug-http'],
				'request.options' => array(
					'exceptions' => false,
				),
				'http_errors' => false,
				'cookies' => true,
				'headers' => [
					'openthc-test-mode' => getenv('OPENTHC_TEST_HASH')
				]
			));
		}

		return $c;

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
