<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Test.php
*	description:	用于辅助测试
*
*	@author Nili
*	@license Apache v2 License
*	
**/		
/**
* 		
*/
class Action_Test 
{
	public function run()
	{
//		session_start();
		var_dump($_SESSION);
		$arr = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'time' => date('Y-m-d H:i:s'),
			'info' => $_SESSION);
		Vera_Log::addLog('tmp', json_encode($arr));
		return true;
	}
}