<?php
/**
 * Application Base Controller
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller;

class Base extends \OpenTHC\Controller\Base
{
	protected $data;

	function __construct($c)
	{
		parent::__construct($c);

		$data = [];
		$data['Site'] = [];
		$data['Page'] = [];
		$data['Page']['title'] = 'OpenTHC';

		$data['CSRF'] = \OpenTHC\SSO\CSRF::getToken();

		$data['OpenTHC'] = [];
		$data['OpenTHC']['cic'] = \OpenTHC\Config::get('openthc/cic');
		$data['OpenTHC']['dir'] = \OpenTHC\Config::get('openthc/dir');

		$this->data = $data;

	}
}
