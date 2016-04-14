<?php
/**
*
*	@copyright  Copyright (c) 2016 JXChen
*	All rights reserved
*
*	file:			Index.php
*	description:    小米运动排行榜首页
*
*	@author JXChen
*	@license Apache v2 License
*
**/

class Action_Index extends Action_Base
{
	function __construct($resource)
	{
		parent::__construct($resource);
	}

	public function run()
	{
		$view = new Vera_View(true);

		$view->display('sports/Index.tpl');
		return true;
	}
}

?>