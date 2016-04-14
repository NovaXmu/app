<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Analysis.php
 *	description:	数据展示面板
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/

/**
 *  数据展示面板
 */
class Action_Board_Panel_Analysis
{

    function __construct()
    {

    }

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $view->display('cms/panel/Analysis.tpl');
        return true;
    }
}

?>
