<?php
/**
 * Base Class for API Testing
 */

namespace Test;

class Done_Test extends \Test\Base_Test_Case
{
	function test_done()
	{
		$c = $this->_ua();
		$res = $c->get('/done');
		$this->assertValidResponse($res);

	}
}
