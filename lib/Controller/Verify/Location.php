<?php
/**
 * Verify Region
 */

namespace App\Controller\Verify;

class Location extends \App\Controller\Verify\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Profile Location';

		$act = $this->loadTicket();

		if (empty($_SESSION['verify-geoip'])) {
			$cfg = \OpenTHC\Config::get('maxmind');
			if (!empty($cfg['account'])) {
				$api = new \GeoIp2\WebService\Client($cfg['account'], $cfg['license-key']);
				$geo = $api->city($_SERVER['REMOTE_ADDR']);
				$_SESSION['verify-geoip'] = $geo->raw;
			}
		}

		if (!empty($_SESSION['verify-geoip'])) {

			$data['iso3166_1_pick'] = [
				'alpha_2' => $_SESSION['verify-geoip']['country']['iso_code'],
				'name' =>  $_SESSION['verify-geoip']['country']['names']['en'],
			];

			$data['iso3166_2_pick'] = [
				'code' => $_SESSION['verify-geoip']['subdivisions'][0]['iso_code'],
				'name' => $_SESSION['verify-geoip']['subdivisions'][0]['names']['en'],
			];

		}

		if (empty($_SESSION['iso3166_1'])) {
			$data['iso3166_1_list'] = $this->_load_iso3166_list();
			return $RES->write( $this->render('verify/location.php', $data) );
		}

		if (empty($_SESSION['iso3166_2'])) {
			$data['iso3166_2_list'] = $this->_load_iso3166_2_list($_SESSION['iso31661_1']['alpha_2']);
			return $RES->write( $this->render('verify/location-2.php', $data) );
		}

		__exit_text('Invalid Request [CVL-057]', 400);

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		switch ($_POST['a']) {
			case 'iso3166-1-save-next':

				$iso3166_1_pick = [];
				$iso3166_1_list = $this->_load_iso3166_list();
				foreach ($iso3166_1_list as $i => $x) {
					if ($x['alpha_2'] == $_POST['contact-country']) {
						$iso3166_1_pick = $x;
						break;
					}
				}
				if (empty($iso3166_1_pick)) {
					__exit_text('Invalid Country Selected [CVR-071]', 400);
				}

				$_SESSION['iso3166_1'] = $iso3166_1_pick;
				unset($_SESSION['iso3166_2']);

				return $RES->withRedirect(sprintf('/verify/location?_=%s', $_GET['_']));

				break;

			case 'iso3166-2-save-next':
				// Save Region 2

				// Find Region Level Two Stuff?
				$iso3166_2_pick = [];
				$iso3166_2_list = $this->_load_iso3166_2_list($_SESSION['iso3166_1']['alpha_2']);
				foreach ($iso3166_2_list as $i => $x) {
					if ($x['code'] == $_POST['contact-iso3166-2']) {
						$iso3166_2_pick = $x;
						break;
					}
				}
				if (empty($iso3166_2_pick)) {
					__exit_text('Invalid Country/Region Selected [CVR-101]', 400);
				}

				$_SESSION['iso3166_2'] = $iso3166_2_pick;

				// Or Save till the end to save?
				$isoA = strtolower($_SESSION['iso3166_1']['alpha_3']);
				$isoB = strtolower(substr($_SESSION['iso3166_2']['code'], 3));

				$iso3166 = sprintf('%s/%s', $isoA, $isoB);

				$act = $this->loadTicket();

				$dbc = $this->_container->DBC_AUTH;
				$sql = 'UPDATE auth_contact SET flag = flag | :f1::int, iso3166 = :iso, tz = :tz WHERE id = :ct0';
				$sql = 'UPDATE auth_contact SET iso3166 = :iso WHERE id = :ct0';
				$dbc->query($sql, [
					':ct0' => $act['contact']['id'],
					':iso' => $iso3166,
				]);

				// Back to main to see what happens
				return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));
		}

		__exit_text('Invalid Request [CVL-124]', 400);
	}

	/**
	 *
	 */
	private function _load_iso3166_list()
	{
		$x = file_get_contents('/usr/share/iso-codes/json/iso_3166-1.json');
		$x = json_decode($x, true);
		$iso3166_1_list = $x['3166-1'];
		uasort($iso3166_1_list, function($a, $b) {
			return strcmp($a['name'], $b['name']);
		});

		return $iso3166_1_list;

	}

	private function _load_iso3166_2_list($iso3166_1_pick=null)
	{
		$x = file_get_contents('/usr/share/iso-codes/json/iso_3166-2.json');
		$x = json_decode($x, true);

		$r = $x['3166-2'];

		// Filter?
		if (!empty($iso3166_1_pick)) {
			$r = array_filter($r, function($v) use ($iso3166_1_pick) {
				$pre_have = substr($v['code'], 0, 2);
				return ($pre_have == $iso3166_1_pick);
			});
		}

		// Now Sort
		uasort($r, function($a, $b) {
			return strcmp($a['name'], $b['name']);
		});

		return $r;

	}

}
