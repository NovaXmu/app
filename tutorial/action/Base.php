<?php
/**
*	@copyright
*
* 	file:    Base.php
*	description: action基类
*
* 	@author linjun
*/
class Action_Base{
	private static $resource = NULL;

	function __construct(){}

	protected static function getResource(){
		return self::$resource;
	}

	protected static function setResource($resource){
		if(empty($resource)){
			return false;
		}

		self::$resource = $resource;
	}
}
?>