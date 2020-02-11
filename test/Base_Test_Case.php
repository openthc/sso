<?php
/**
 * Base Class for API Testing
 */

namespace Test;

class Base_Test_Case extends \PHPUnit\Framework\TestCase
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
			'base_uri' => TEST_SITE,
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		return $c;

	}


	function assertValidResponse($res, $code=200, $dump=null)
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
		$type = $res->getHeaderLine('content-type');
		$type = strtok($type, ';');
		$this->assertEquals('text/html', $type);

		// $ret = \json_decode($this->raw, true);

		// $this->assertIsArray($ret);
		// // $this->assertArrayHasKey('data', $ret);
		// // $this->assertArrayHasKey('meta', $ret);

		// $this->assertArrayNotHasKey('status', $ret);
		// $this->assertArrayNotHasKey('result', $ret);

		return $this->raw;
	}

}
