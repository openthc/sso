<?php
/**
 * Base Class for API Testing
 */

namespace Test;

class Base_Case extends \PHPUnit\Framework\TestCase
{
	protected $_pid = null;

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
		$c = new \GuzzleHttp\Client(array(
			'base_uri' => sprintf('https://%s', getenv('OPENTHC_TEST_HOST')),
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		// $test_secret = \OpenTHC\Config::get('application_test.secret');
		// $this->assertNotEmpty($test_secret);

		return $c;

	}


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

}
