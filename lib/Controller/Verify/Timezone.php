<?php
/**
 * Verify Region
 */

namespace App\Controller\Verify;

class Timezone extends \App\Controller\Verify\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verify Profile Timezone';

		$act = $this->loadTicket();

		if (empty($_SESSION['iso3166']['code2'])) {
			$data['time_zone_list'] = \DateTimeZone::listIdentifiers();
		} else {
			$data['time_zone_list'] = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $_SESSION['iso3166']['code2']);
		}

		$data['time_zone_pick'] = $_SESSION['geoip']['location']['time_zone'];

		return $RES->write( $this->render('verify/timezone.php', $data) );

	}

	/**
	 *
	 */
	function post($REQ, $RES, $ARG)
	{
		$act = $this->loadTicket();
		$time_zone_list = \DateTimeZone::listIdentifiers(); // \DateTimeZone::PER_COUNTRY, $_SESSION['iso3166_1']['alpha_2']);
		$time_zone_pick = $_POST['contact-timezone'];
		if (!in_array($time_zone_pick, $time_zone_list)) {
			__exit_text('Invalid Timezone [CVT-030]', 400);
		}

		$dbc = $this->_container->DBC_AUTH;
		$sql = 'UPDATE auth_contact SET tz = :tz1 WHERE id = :ct0';
		$dbc->query($sql, [
			':ct0' => $act['contact']['id'],
			':tz1' => $time_zone_pick,
		]);

		return $RES->withRedirect(sprintf('/verify?_=%s', $_GET['_']));

	}

}
