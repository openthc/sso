<?php
/**
 */

namespace Test\A_System;

class A_System_Test extends \Test\Base_Case
{
	function test_system()
	{
		$x = function_exists('geoip_record_by_name');
		$this->assertEmpty($x);

	}
}
