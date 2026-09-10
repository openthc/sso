<?php
/**
 * Verify Region
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

class Location extends \OpenTHC\SSO\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Profile Location';

		$act = $this->loadTicket();
		$this->loadGeoIP();

		if ( ! empty($_SESSION['iso3166_1'])) {
			$data['iso3166_1_pick'] = $_SESSION['iso3166_1'];
		}

		if ( ! empty($_SESSION['iso3166_2'])) {
			$data['iso3166_2_pick'] = $_SESSION['iso3166_2'];
		}

		// Pick Top Level ISO
		if (empty($_SESSION['iso3166_1_pick'])) {
			$data['iso3166_1_list'] = $this->_load_iso3166_list();
			return $RES->write( $this->render('verify/location.php', $data) );
		}

		// Pick Second Level ISO
		if (empty($_SESSION['iso3166_2_pick'])) {
			$data['iso3166_2_list'] = $this->_load_iso3166_2_list($_SESSION['iso3166_1_pick']['id']);
			return $RES->write( $this->render('verify/location-2.php', $data) );
		}

		return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();

		switch ($_POST['a']) {
			case 'geo-resolve':
				return $this->_geo_resolve($RES);
			case 'iso3166-1-save-next':

				$iso3166_1_pick = [];
				$iso3166_1_list = $this->_load_iso3166_list();
				foreach ($iso3166_1_list as $i => $x) {
					if ($x['id'] == $_POST['contact-iso3166-1']) {
						$iso3166_1_pick = $x;
						break;
					}
				}
				if (empty($iso3166_1_pick)) {
					__exit_text('Invalid Country Selected [CVR-071]', 400);
				}

				$_SESSION['iso3166_1_pick'] = $iso3166_1_pick;

				return $RES->withRedirect(sprintf('/verify/location?_=%s', $_GET['_']));

				break;

			case 'iso3166-2-save-next': // Save Region 2

				// Find Region Level Two Stuff?
				$iso3166_2_pick = [];
				$iso3166_2_list = $this->_load_iso3166_2_list($_SESSION['iso3166_1_pick']['id']);
				foreach ($iso3166_2_list as $i => $x) {
					if ($x['id'] == $_POST['contact-iso3166-2']) {
						$iso3166_2_pick = $x;
						break;
					}
				}
				if (empty($iso3166_2_pick)) {
					__exit_text('Invalid Country/Region Selected [CVR-101]', 400);
				}

				$_SESSION['iso3166_2_pick'] = $iso3166_2_pick;

				$dbc = $this->_container->DBC_AUTH;
				$sql = 'UPDATE auth_contact SET flag = flag | :f1::int, iso3166 = :iso, tz = :tz WHERE id = :ct0';
				$sql = 'UPDATE auth_contact SET iso3166 = :iso WHERE id = :ct0';
				$dbc->query($sql, [
					':ct0' => $act['contact']['id'],
					':iso' => $iso3166_2_pick['id'],
				]);

				$dbc->insert('log_event', [
					'contact_id' => $act['contact']['id'],
					'code' => 'Contact/Location/Update',
					'meta' => json_encode($_SESSION),
				]);

				// Back to main to see what happens
				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
		}

		__exit_text('Invalid Request [CVL-124]', 400);
	}

	/**
	 *
	 */
	private function _geo_resolve($RES)
	{
		$cfg = \OpenTHC\Config::get('opencage');
		// __exit_json($cfg);

		$arg = [
			'key' => $cfg['api-key'],
			'q' => sprintf('%f+%f', $_POST['lat'], $_POST['lon']),
		];
		$url = sprintf('https://api.opencagedata.com/geocode/v1/json?%s', http_build_query($arg));
		// __exit_text($url);
		$req = __curl_init($url);
		$res = curl_exec($req);
		$res = json_decode($res);
		$inf = curl_getinfo($req);
		curl_close($req);

		if ( ! empty($res->results[0])) {

			$res = $res->results[0];

			$_SESSION['iso3166_1'] = [
				'id' => $res->components->{'ISO_3166-1_alpha-2'},
				'name' => '',
			];
			$_SESSION['iso3166_2'] = [
				'id' => $res->components->{'ISO_3166-2'}[0],
				'name' => '',
			];

			__exit_text([
				'data' => [
					'iso3166_1' => $_SESSION['iso3166_1'],
					'iso3166_2' => $_SESSION['iso3166_2'],
				],
				'meta' => $res,
			]);
		}

		__exit_json([
			'data' => null,
			'meta' => [ 'note' => 'Not Resolved' ]
		]);

	}

	/**
	 *
	 */
	private function _load_iso3166_list()
	{
		$path = '/usr/share/iso-codes/json/iso_3166-1.json';
		if (!is_file($path)) {
			return [];
		}

		$data = file_get_contents($path);
		$json = json_decode($data, true);
		if (empty($json) || empty($json['3166-1'])) {
			return [];
		}

		$res = [];
		foreach ($json['3166-1'] as $item) {
			$res[] = [
				'id'    => $item['alpha_2'],
				'code2' => $item['alpha_2'],
				'code3' => $item['alpha_3'],
				'name'  => $item['name']
			];
		}

		usort($res, function($a, $b) {
			return strcasecmp($a['name'], $b['name']);
		});

		return $res;
	}

	private function _load_iso3166_2_list($iso3166_1_pick=null)
	{
		$path = '/usr/share/iso-codes/json/iso_3166-2.json';
		if (!is_file($path)) {
			return [];
		}

		$data = file_get_contents($path);
		$json = json_decode($data, true);
		if (empty($json) || empty($json['3166-2'])) {
			return [];
		}

		$res = [];
		foreach ($json['3166-2'] as $item) {
			$code2 = explode('-', $item['code'])[0];

			if (!empty($iso3166_1_pick) && $code2 !== $iso3166_1_pick) {
				continue;
			}

			$res[] = [
				'id'    => $item['code'],
				'code2' => $code2,
				'code3' => null,
				'name'  => $item['name']
			];
		}

		usort($res, function($a, $b) {
			return strcasecmp($a['name'], $b['name']);
		});

		return $res;
	}

}
