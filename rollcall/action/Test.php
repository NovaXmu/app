<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Test.php
*	description:	测试Action，无实际意义
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
	
	public function run () 
	{
		var_dump(Service_Func::checkLimitConds($_GET['num'], array('grade' => $_GET['cond'])));
	}
}