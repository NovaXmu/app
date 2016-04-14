<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Earn.php
*	description:	博饼赚网薪相关
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Earn extends Action_Base
{
	
	function __construct()
	{
			
	}
	public function run () {
		
		
		if (!isset($_GET['m']))
		{
			$resource = $this->getResource();
			$view = new Vera_View(true);
			$view->assign('personInfo', $resource);
			$view->display("mall/GameList.tpl");
			return true;
		}

		switch ($_GET['m']) {
			case 'bobing':
				return $this->bobing();
				break;

			case 'game2048':
				return $this->game2048();
				break;
			
			default:
				# code...
				break;
		}

		return false;
		
	}

	public function bobing() {
		$resource = $this->getResource();
		$service = new Service_Earn();
		$times = $service->getRemainBobingTimes();
		$view = new Vera_View(true);
		$view->assign('personInfo', $resource);
		$view->assign("times",$times);
		$view->display("mall/Bobing.tpl");
		return true;
	}

	public function game2048(){		
		$view = new Vera_View(true);
		$resource = $this->getResource();
		$view->assign('personInfo', $resource);
		$view->display("mall/Game2048.tpl");
	}
}
?>