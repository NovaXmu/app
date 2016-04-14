<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Index.php
*	description:	首页
*
*	@author Nili
*	@license Apache v2 License
*	
**/
/**
* 
*/
class Action_Index
{
	
	public function run ()
	{
		header("location:/templates/cargo/dist/index.html");
		exit();
	}
}