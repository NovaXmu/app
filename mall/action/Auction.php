<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Auction.php
*	description:	竞价商城相关
*
*	@author Nili
*	@license Apache v2 License
*	
**/

class Action_Auction extends Action_Base
{
    function __construct() {}

    public function run()
    {
    	if(!isset($_GET['m']))
    	{
    		return false;
    	}
    	switch ($_GET['m']) {
    		case 'list':
    			return $this->getList();
    			break;
            case 'auction1':
                return $this->auction1();
                break;
    	}
        return false;
    }

    public function getList(){
		$list = Service_Helper::getList(0);
		
		$view = new Vera_View(true);//设置为true开启debug模式
        $view->assign("list",$list);
        $view->assign("personInfo", $_SESSION['yb_user_info']);
        $view->display('mall/AuctionList.tpl');
        return true;
    }

    public function auction1() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
            return false;
        }

        $detail = Service_Helper::getDetail($_GET['id']);
        $view = new Vera_View(true);//设置为true开启debug模式
        $view->assign("detail", $detail);
        $view->assign("personInfo", $_SESSION['yb_user_info']);
        $view->display('mall/Auction.tpl');
        return true;
    }
}

?>