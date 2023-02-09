<?php
/**
 * Verify Done
 * The user has onboarded themselves as much as they can, and now must wait.
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Verify;

use OpenTHC\Contact;

use OpenTHC\SSO\Auth_Contact;

class Done extends \OpenTHC\SSO\Controller\Verify\Base
{

	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARGS)
	{
		$data = $this->data;
		$data['Page']['title'] = 'Verification Complete';
		$data['Company'] = $RES->getAttribute('Company');
		return $RES->write( $this->render('verify/done.php', $data) );
	}
}
