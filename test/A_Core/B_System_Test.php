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

	function test_iso()
	{
		$f = '/usr/share/iso-codes/json/iso_3166-1.json';
		$this->assertTrue(is_file($f));

		$f = '/usr/share/iso-codes/json/iso_3166-2.json';
		$this->assertTrue(is_file($f));

	}
}
