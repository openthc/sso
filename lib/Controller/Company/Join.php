<?php
/**
 * Join a Company
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Company;

use Edoceo\Radix\Session;

class Join extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG) {

		$data = $this->data;
		$data['Page'] = [];
		$data['Page']['title'] = 'Company / Join';

		$RES->getBody()->write( $this->render('company/join.php', $data) );

		return $RES;

	}

	/**
	 * Save Data and Tell them Done
	 * Save Somewhere for OPS to Verify and Activate?
	 */
	function post($REQ, $RES, $ARG)
	{
		$this->_container->RDB->publish('sso', json_encode([
			'event' => 'company/join',
			'_SESSION' => $_SESSION,
		]));

		if (empty($_POST['company-name']) && empty($_POST['company-id'])) {
			Session::flash('fail', 'Invalid Request, Try Again [CCJ-036]');
			return $this->redirect('/company/join');
		}

		$dbc_auth = $this->dic->get('DBC_AUTH');
		$dbc_main = $this->dic->get('DBC_MAIN');

		$Company0 = [];
		$Company1 = [];

		if ( ! empty($_POST['company-id'])) {

			$Company0 = $dbc_main->fetchRow('SELECT id, name, stat, iso3166, tz FROM company WHERE id = :c0', [
				':c0' => $_POST['company-id'],
			]);

			$Company1 = $dbc_auth->fetchRow('SELECT id FROM auth_company WHERE id = :c0', [
				':c0' => $_POST['company-id'],
			]);

		}

		// Nothing in Main but something in Auth?
		if (empty($Company0['id']) && ! empty($Company1['id'])) {
			throw new \Exception('Unexpected Company Status [CCJ-094]');
		}

		// Existing in Main and Auth
		if ( ! empty($Company0['id']) && ! empty($Company1['id'])) {
			Session::flash('warn', 'This Company profile already exists [CCJ-068]');
			Session::flash('warn', 'Join a different company, or ask this company to invite you');
			return $this->redirect('/company/join');
		}

		// Existing in Main and Missing Auth
		if ( ! empty($Company0['id']) && empty($Company1['id'])) {

			// Create in Auth
			$Company1 = [];
			$Company1['id'] = $Company0['id'];
			$Company1['stat'] = $Company0['stat'] ?: 102;
			$Company1['name'] = $Company0['name'];
			// $Company1['code'] = $Company0['guid'];
			$dbc_auth->insert('auth_company', $Company1);

			// Link Contact To Company
			$dbc_auth->insert('auth_company_contact', [
				'company_id' => $Company1['id'],
				'contact_id' => $_SESSION['Contact']['id'],
				'stat' => 102,
			]);

			Session::flash('info', 'Request to Join Company has been submitted');

			return $this->redirect('/profile');
		}

		// New Request
		if (empty($Company0['id']) && empty($Company1['id'])) {

			// Create in Auth
			$Company0 = [];
			$Company0['id'] = _ulid();
			$Company0['stat'] = 102;
			$Company0['name'] = substr($_POST['company-name'], 0, 256);
			// $Company0['code'] = substr($_POST['company-guid'], 0, 64);
			$dbc_auth->insert('auth_company', $Company0);

			// Link Contact To Company
			$dbc_auth->insert('auth_company_contact', [
				'company_id' => $Company0['id'],
				'contact_id' => $_SESSION['Contact']['id'],
				'stat' => 102,
			]);

			// Create in Main
			$Company0['guid'] = substr($_POST['company-guid'], 0, 64);
			$Company0['iso3166'] = substr($_POST['company-region'], 0, 8);
			$dbc_main->insert('company', $Company0);

			Session::flash('info', 'Request to Join Company has been submitted');

			return $this->redirect('/profile');

		}

		return $this->redirect('/done?e=CCJ-030');

	}

}
