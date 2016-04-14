<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Person.php
*	description:	个人商城相关
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Person extends Action_Base
{
	
	function __construct()
	{
		
	}

	public function run () {
		$resource = $this->getResource();
		$data = Service_Person::getPersonLog($resource['yb_userid']);
		$totalAward = Service_Person::getAward($resource['yb_userid']);
		$log = Service_Person::getAwardLog($resource['yb_userid']);
		$view = new Vera_View(true);
		$view->assign("personInfo", $resource);
		$view->assign("totalAward", $totalAward);
		$view->assign("data", $data);
		$view->assign("log", $log);
		$view->display("mall/Mine.tpl");
	}
}
?>