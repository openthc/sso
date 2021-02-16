<?php
/**
 * Application Base Controller
 */

namespace App\Controller;

class Base extends \OpenTHC\Controller\Base
{
	protected $data;

	function __construct($c)
	{
		parent::__construct($c);

		$data = [];
		$data['Site'] = [];
		$data['Site']['test'] = $_ENV['test'];
		$data['Page'] = [];
		$data['Page']['title'] = 'OpenTHC';

		$data['OpenTHC'] = [];
		$data['OpenTHC']['cic'] = \OpenTHC\Config::get('openthc_cic');
		$data['OpenTHC']['dir'] = \OpenTHC\Config::get('openthc_dir');

		$this->data = $data;

	}
}
