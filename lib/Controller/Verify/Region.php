<?php
/**
 * Verify Region
 */

namespace App\Controller\Verify;

class Region extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Profile Region';

		$dbc = $this->_container->DBC_AUTH;

		if (empty($_SESSION['verify-geoip'])) {
			$cfg = \OpenTHC\Config::get('maxmind');
			if (!empty($cfg['account'])) {
				$api = new \GeoIp2\WebService\Client($cfg['account'], $cfg['license-key']);
				$geo = $api->city($_SERVER['REMOTE_ADDR']);
				$_SESSION['verify-geoip'] = $geo->raw;
			}
		}

		if (!empty($_SESSION['verify-geoip'])) {
			$data['region']['iso3166_1'] = [
				'code' => $_SESSION['verify-geoip']['country']['iso_code'],
				'name' =>  $_SESSION['verify-geoip']['country']['names']['en'],
			];
			// Search
			$data['region']['iso3166_2'] = [
				'code' => $_SESSION['verify-geoip']['subdivisions'][0]['iso_code'],
				'name' => $_SESSION['verify-geoip']['subdivisions'][0]['names']['en'],
			];
		}

		$data['time_zone_list'] = \DateTimeZone::listIdentifiers();
		$data['time_zone_pick'] = $_SESSION['verify-geoip']['location']['time_zone'];

		// __exit_text($data, 501);

		return $this->_container->view->render($RES, 'page/verify/region.html', $data);
	}

	function post($REQ, $RES, $ARG)
	{

		switch ($_POST['a']) {
			case 'save-next':
				$sql = 'UPDATE auth_contact SET flag = flag | :f1::int, iso3166 = :iso, tz = :tz WHERE id = :ct0';
				$sql = 'UPDATE auth_contact SET iso3166 = :iso, tz = :tz WHERE id = :ct0';
				$dbc_auth->query($sql, [
					':ct0' => $act['contact']['id'],
					// ':f1' => \App\Contact::FLAG_REGION
					':iso' => $_POST['iso3166'],
					':tz' => $_POST['tz'],
				]);
				break;
		}

		__exit_text('Invalid Request [CVR-053]', 400);
	}
}
