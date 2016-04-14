<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Wx.php
*	description:	网薪相关
*
*	@author Nili
*	@license Apache v2 License
*	
**/
class Action_Board_Panel_Wx
{

    function __construct()
    {

    }

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $view->display('cms/panel/Wx.tpl');
        return true;
    }
}
