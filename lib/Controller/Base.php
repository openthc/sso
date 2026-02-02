<?php
/**
 * Application Base Controller
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller;

use Psr\Container\ContainerInterface;

use OpenTHC\CSRF;

class Base extends \OpenTHC\Controller\Base
{
	protected $dic;

	protected $data;

	/**
	 * Constructor
	 */
	function __construct(ContainerInterface $dic)
	{
		$this->dic = $dic;
		// parent::__construct($c);

		$data = [];
		$data['Site'] = [];
		$data['Page'] = [];
		$data['Page']['title'] = 'OpenTHC';

		$data['CSRF'] = CSRF::getToken();

		$this->data = $data;

	}

	protected function redirect($path, $code=302)
	{
		$RES = new \GuzzleHttp\Psr7\Response($code);
		return $RES->withHeader('Location', $path);
	}

	/**
	 * Sends an Failure Response
	 */
	protected function sendFailure($RES, $data, $code=400)
	{
		// $type_want
		$RES = $RES->withBody(new \Slim\Http\RequestBody());
		$RES = $RES->withStatus($code);
		$RES = $RES->getBody()->write( $this->render('done.php', $data) );
		return $RES;
		// if want JSON?
		// return $RES->withJSON([
		// 	'data' => null,
		// 	'meta' => [ 'note' => ' []' ]
		// ], $code);
	}

	/**
	 * Load GeoIP Data to Session
	 */
	protected function loadGeoIP() : void
	{
		// Would like to put this behind a cache
		if ( ! empty($_SESSION['geoip'])) {
			return;
		}

		$cfg = \OpenTHC\Config::get('maxmind');
		if (empty($cfg['account'])) {
			return;
		}

		$api = new \GeoIp2\WebService\Client($cfg['account'], $cfg['license-key']);
		$geo = $api->city($_SERVER['REMOTE_ADDR']);
		$raw = $geo->raw;

		$_SESSION['geoip'] = true;

		$_SESSION['iso3166_1'] = [
			'id' => $raw['country']['iso_code'],
			'name' => $raw['country']['names']['en'],
		];

		$_SESSION['iso3166_2'] = [
			'id' => sprintf('%s-%s', $raw['country']['iso_code'], $raw['subdivisions'][0]['iso_code']),
			'name' => $raw['subdivisions'][0]['names']['en']
		];

		$_SESSION['tz'] = $raw['location']['time_zone'];

	}

}
