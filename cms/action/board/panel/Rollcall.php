<?php 
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Rollcall.php
*	description:	会场签到面板
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
*  会场签到面板
*/
class Action_Board_Panel_Rollcall
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式
        $data = new Data_Rollcall();

        $view->assign('needReview', $data->getNeedReviewActs());//待审核列表

        $view->caching = Smarty::CACHING_OFF;
        $view->display('cms/panel/Rollcall.tpl');
        return true;
    }
}

?>