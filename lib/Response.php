<?php
/**
 * PSR7 Reponse w/Features
 */

namespace App;

class Response extends \Slim\Http\Response
{
	private $_attr = [];

	function getAttribute($key)
	{
		return $this->_attr[$key];
	}

	function withAttribute($key, $val)
	{
		$obj1 = clone $this;
		$obj1->_attr[ $key ] = $val;
		return $obj1;
	}

	function withJSON($data, $code=null, $flag=null)
	{
		if (empty($flag)) {
			$flag = JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
		}

		return parent::withJSON($data, $code, $flag);
	}

}
