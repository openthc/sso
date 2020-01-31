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

	// @todo Use Improved Encoding Options
	// function withJson($data, $status=null, $encodingOptions=0)
	// {
	// 	$RES = $this->withBody(new Body(fopen('php://temp', 'r+')));
	// 	$response->body->write($json = json_encode($data, $encodingOptions));
	// }

}
