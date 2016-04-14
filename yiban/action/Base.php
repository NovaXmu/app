<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Base.php
*	description:	action基类
*
*	@author Nili
*	@license Apache v2 License
*	
**/

class Action_Base
{
	private static $resource = NULL;

	function __construct($resource)
	{
		self::$resource = $resource;
	}

	protected static function getResource()
	{
		return self::$resource;
	}

	protected static function setResource($resource)
	{
		if(empty($resource))
			return false;

		self::$resource = $resource;
	}
}
?>