<?php
/**
 * Test Location Controller Helper Methods
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\Unit;

class Location_Controller_Test extends \OpenTHC\SSO\Test\Base
{
	/**
	 * Test that _load_iso3166_list parses the JSON file and returns correct sorted results.
	 */
	function test_load_iso3166_list()
	{
		$container = new \Slim\Container();
		$controller = new \OpenTHC\SSO\Controller\Verify\Location($container);

		$ref = new \ReflectionClass($controller);
		$method = $ref->getMethod('_load_iso3166_list');
		$method->setAccessible(true);

		$res = $method->invoke($controller);

		$this->assertIsArray($res);
		$this->assertNotEmpty($res);

		// Verify fields of the first entry
		$first = $res[0];
		$this->assertArrayHasKey('id', $first);
		$this->assertArrayHasKey('code2', $first);
		$this->assertArrayHasKey('code3', $first);
		$this->assertArrayHasKey('name', $first);

		// Verify alphabetical sorting
		$prev_name = '';
		foreach ($res as $item) {
			$this->assertNotEmpty($item['id']);
			$this->assertNotEmpty($item['name']);
			$this->assertTrue(strcasecmp($item['name'], $prev_name) >= 0, sprintf("Expected '%s' to sort after '%s'", $item['name'], $prev_name));
			$prev_name = $item['name'];
		}
	}

	/**
	 * Test that _load_iso3166_2_list parses the JSON file and supports filtering.
	 */
	function test_load_iso3166_2_list()
	{
		$container = new \Slim\Container();
		$controller = new \OpenTHC\SSO\Controller\Verify\Location($container);

		$ref = new \ReflectionClass($controller);
		$method = $ref->getMethod('_load_iso3166_2_list');
		$method->setAccessible(true);

		// Test unfiltered (all subdivisions)
		$res_all = $method->invoke($controller);
		$this->assertIsArray($res_all);
		$this->assertNotEmpty($res_all);

		$first = $res_all[0];
		$this->assertArrayHasKey('id', $first);
		$this->assertArrayHasKey('code2', $first);
		$this->assertArrayHasKey('code3', $first);
		$this->assertArrayHasKey('name', $first);

		// Test filtered by US (United States)
		$res_us = $method->invoke($controller, 'US');
		$this->assertIsArray($res_us);
		$this->assertNotEmpty($res_us);

		foreach ($res_us as $item) {
			$this->assertEquals('US', $item['code2']);
			$this->assertStringStartsWith('US-', $item['id']);
		}

		// Verify sorting
		$prev_name = '';
		foreach ($res_us as $item) {
			$this->assertTrue(strcasecmp($item['name'], $prev_name) >= 0, sprintf("Expected '%s' to sort after '%s'", $item['name'], $prev_name));
			$prev_name = $item['name'];
		}
	}
}
