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

		if (!empty($_SESSION['iso3166_1'])) {
			$data['iso3166_1_pick'] = $_SESSION['iso3166_1'];
		}

		if (!empty($_SESSION['iso3166_2'])) {
			$data['iso3166_2_pick'] = $_SESSION['iso3166_2'];
		}

		if (empty($_SESSION['iso3166_1_pick'])) {
			$data['iso3166_1_list'] = $this->_load_iso3166_list();
			return $RES->write( $this->render('verify/location.php', $data) );
		}

		if (empty($_SESSION['iso3166_2_pick'])) {
			$data['iso3166_2_list'] = $this->_load_iso3166_2_list($_SESSION['iso3166_1_pick']['id']);
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

				// Or Save till the end to save?
				$act = $this->loadTicket();

				$dbc = $this->_container->DBC_AUTH;
				$sql = 'UPDATE auth_contact SET flag = flag | :f1::int, iso3166 = :iso, tz = :tz WHERE id = :ct0';
				$sql = 'UPDATE auth_contact SET iso3166 = :iso WHERE id = :ct0';
				$dbc->query($sql, [
					':ct0' => $act['contact']['id'],
					':iso' => $iso3166_2_pick['id'],
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
		$dbc = $this->_container->DBC_AUTH;
		$res = $dbc->fetchAll("SELECT id, code2, code3, name FROM iso3166 WHERE type = 'Country' ORDER BY name");
		return $res;
	}

	private function _load_iso3166_2_list($iso3166_1_pick=null)
	{
		$dbc = $this->_container->DBC_AUTH;

		$sql = "SELECT id, code2, code3, name FROM iso3166 WHERE type != 'Country' ORDER BY name";
		$arg = [];

		// Filter?
		if (!empty($iso3166_1_pick)) {
			$sql = "SELECT id, code2, code3, name FROM iso3166 WHERE code2 = :c2 AND type != 'Country' ORDER BY name";
			$arg = [ ':c2' => $iso3166_1_pick ];
		}

		$res = $dbc->fetchAll($sql, $arg);

		return $res;

	}

}
