<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Exchange.php
*	description:	网薪商城兑换
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Exchange extends Action_Base
{
	
	function __construct(){
	}

	public function run()
    {
    	if(!isset($_GET['m']))
    	{
    		return false;
    	}
    	switch ($_GET['m']) {
    		case 'list':
    			$this->getList();
    			break;
            case 'exchange1':
                $this->exchange1();
                break;
    		default:
    			# code...
    			break;
    	}
        return true;
    }

    public function getList(){
		$list = Service_Helper::getList(1);
		$view = new Vera_View(true);//设置为true开启debug模式
        $view->assign("list",$list);
        $view->assign("personInfo", $_SESSION['yb_user_info']);
        $view->display('mall/ExchangeList.tpl');
        return true;
    }

    public function exchange1() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
            return false;
        }

        $detail = Service_Helper::getDetail($_GET['id']);
        $view = new Vera_View(true);//设置为true开启debug模式
        $view->assign("detail", $detail);
        $view->assign("personInfo", $_SESSION['yb_user_info']);
        $view->display('mall/Exchange.tpl');
        return true;
    }
}

?>