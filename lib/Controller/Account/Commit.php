<?php
/**
 * Account Create Confirm
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller\Account;

use OpenTHC\SSO\Auth_Context_Ticket;

class Commit extends \OpenTHC\SSO\Controller\Base
{
	/**
	 * Account Create Confirm
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$act_data = \OpenTHC\SSO\Auth_Context_Ticket::get($_GET['_']);
		if (empty($act_data)) {
			throw new \Exception('Invalid Request [CAC-022]');
		}

		// Post the Contact to the API
		$arg_contact = $act_data['contact'];

		switch ($act_data['intent']) {
			case 'account-create':
				$arg_contact['email_verify'] = true;
				break;
		}

		// $sso = new \OpenTHC\Service('sso');
		$sso = new \OpenTHC\Service\OpenTHC('sso');
		$res = $sso->post('/api/contact', [ 'form_params' => $arg_contact ]);

		$ret_args = [];

		switch ($res['code']) {
			case 200:
			case 201:
			case 208:
				$ret_args['e'] = 'CAC-111';
				break;
			default:
				$ret_args['c'] = $res['code'];
				$ret_args['e'] = $res['data']['code'];
				break;
		}

		$RES = $RES->withAttribute('Contact', [
			'id' => $res['data']['id'],
		]);

		$act_data['contact'] = $res['data'];

		unset($act_data['account']);

		// Verify after Create
		$tok = \OpenTHC\SSO\Auth_Context_Ticket::set($act_data);
		return $RES->withRedirect(sprintf('/verify?_=%s', $tok));

	}

}
