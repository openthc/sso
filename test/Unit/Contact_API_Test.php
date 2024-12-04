<?php
/**
 * Test Contact API
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Test\Unit;

class Contact_API_Test extends \OpenTHC\SSO\Test\Base
{
	protected $Contact;

	function setup() : void
	{
		$host = parse_url($_ENV['OPENTHC_TEST_ORIGIN'], PHP_URL_HOST);

		$this->Contact = [
			'id' => '',
			'name' => sprintf('Test Contact %06x', $this->_pid),
			'email' => strtolower(sprintf('test+%s@%s', _ulid(), $host)),
			'phone' => '1324567890',
		];
	}

	/**
	 * Test service connection to App
	 */
	function test_create_and_commit()
	{
		$sso = new \OpenTHC\Service\OpenTHC('sso');
		$res = $sso->post('/api/contact', [ 'form_params' => [
			'name' => $this->Contact['name'],
			'email' => $this->Contact['email'],
			'phone' => $this->Contact['phone'],
		]]);
		$res = $this->assertValidAPIResponse($res, 201);
		$this->assertNotEmpty($res['data']['id']);
		$this->assertEquals(0, $res['data']['flag']);
		$this->assertEquals(100, $res['data']['stat']);

		$url = sprintf('/api/contact/%s', $res['data']['id']);
		$res = $sso->post($url, [ 'form_params' => [
			'stat' => 102,
			'email_verify' => true,
		]]);
		$res = $this->assertValidAPIResponse($res);
		$this->assertNotEmpty($res['data']['id']);
		$this->assertEquals(1, $res['data']['flag']);
		$this->assertEquals(102, $res['data']['stat']);
	}

	function test_create_and_retry()
	{
		$sso = new \OpenTHC\Service\OpenTHC('sso');
		$res = $sso->post('/api/contact', [ 'form_params' => [
			'name' => $this->Contact['name'],
			'email' => $this->Contact['email'],
			'phone' => $this->Contact['phone'],
		]]);
		$res = $this->assertValidAPIResponse($res, 201);

		$res = $sso->post('/api/contact', [ 'form_params' => [
			'name' => $this->Contact['name'],
			'email' => $this->Contact['email'],
			'phone' => $this->Contact['phone'],
		]]);
		$res = $this->assertValidAPIResponse($res, 409);


	}

	function test_create_verify_done()
	{
		$sso = new \OpenTHC\Service\OpenTHC('sso');
		$res = $sso->post('/api/contact', [ 'form_params' => [
			'name' => $this->Contact['name'],
			'email' => $this->Contact['email'],
			'phone' => $this->Contact['phone'],
		]]);
		$res = $this->assertValidAPIResponse($res, 201);

		$dbc = $this->_dbc();
		$sql = <<<SQL
		UPDATE auth_contact SET flag = 1, stat = 200, iso3166 = 'US-WA', tz = 'America/Los_Angeles'
		WHERE id = :c0
		SQL;
		$dbc->query($sql, [
			':c0' => $res['data']['id'],
		]);

		// Try Second time after committed
		$res = $sso->post('/api/contact', [ 'form_params' => [
			'name' => $this->Contact['name'],
			'email' => $this->Contact['email'],
			'phone' => $this->Contact['phone'],
		]]);
		// var_dump($res);
		$res = $this->assertValidAPIResponse($res, 409);
		$this->assertEquals(200, $res['data']['stat']);
		$this->assertIsArray($res['meta']);
		$this->assertEquals('CAC-065', $res['meta']['code']);

		// $url = sprintf('/api/contact/%s', $res['data']['id']);
		// $res = $sso->post($url, [ 'form_params' => [
		// 	'email_verify' => true,
		// ]]);
		// $res = $this->assertValidAPIResponse($res);
		// $this->assertNotEmpty($res['data']['id']);
		// $this->assertEquals(1, $res['data']['flag']);
		// $this->assertEquals(102, $res['data']['stat']);
		// var_dump($res);

	}

}
