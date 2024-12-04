<?php
/**
 * Join a Company
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Company;

class Join extends \OpenTHC\SSO\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG) {

		$data = $this->data;
		$data['Page'] = [];
		$data['Page']['title'] = 'Company :: Join';

		return $RES->write( $this->render('company/join.php', $data) );

	}

	function post($REQ, $RES, $ARG) {


	}

}
