<?php
/**
 */

namespace Test\A_Core;

class B_System_Test extends \Test\Base_Case
{
	function test_system()
	{
		$x = function_exists('geoip_record_by_name');
		$this->assertEmpty($x);

	}
}
