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
			$RES->getBody()->write( $this->render('verify/location.php', $data) );
			return $RES;
		}

		// Pick Second Level ISO
		if (empty($_SESSION['iso3166_2_pick'])) {
			$data['iso3166_2_list'] = $this->_load_iso3166_2_list($_SESSION['iso3166_1_pick']['id']);
			$RES->getBody()->write( $this->render('verify/location-2.php', $data) );
			return $RES;
		}

		return $this->redirect(sprintf('/verify?_=%s', $_GET['_']));

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

				return $this->redirect(sprintf('/verify/location?_=%s', $_GET['_']));

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

				$dbc_auth = $this->dic->get('DBC_AUTH');

				$sql = 'UPDATE auth_contact SET flag = flag | :f1::int, iso3166 = :iso, tz = :tz WHERE id = :ct0';
				$sql = 'UPDATE auth_contact SET iso3166 = :iso WHERE id = :ct0';
				$dbc_auth->query($sql, [
					':ct0' => $act['contact']['id'],
					':iso' => $iso3166_2_pick['id'],
				]);

				$dbc_auth->insert('log_event', [
					'contact_id' => $act['contact']['id'],
					'code' => 'Contact/Location/Update',
					'meta' => json_encode($_SESSION),
				]);

				// Back to main to see what happens
				return $this->redirect(sprintf('/verify?_=%s', $_GET['_']));
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
		$dbc_auth = $this->dic->get('DBC_AUTH');
		$res = $dbc_auth->fetchAll("SELECT id, code2, code3, name FROM iso3166 WHERE type = 'Country' ORDER BY name");
		return $res;
	}

	private function _load_iso3166_2_list($iso3166_1_pick=null)
	{
		$dbc_auth = $this->dic->get('DBC_AUTH');

		$sql = "SELECT id, code2, code3, name FROM iso3166 WHERE type != 'Country' ORDER BY name";
		$arg = [];

		// Filter?
		if (!empty($iso3166_1_pick)) {
			$sql = "SELECT id, code2, code3, name FROM iso3166 WHERE code2 = :c2 AND type != 'Country' ORDER BY name";
			$arg = [ ':c2' => $iso3166_1_pick ];
		}

		$res = $dbc_auth->fetchAll($sql, $arg);

		return $res;

	}

}
