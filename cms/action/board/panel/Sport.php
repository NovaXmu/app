<?php
/**
*
*    @copyright  Copyright (c) 2015 echo Lin 
*    All rights reserved
*
*    file:            Sport.php
*    description:    权威性验证action
*
*    @author Linjun
*    @license Apache v2 License
*
**/

/**校运会积分榜面板*/
class Action_Board_Panel_Sport{
    function __construct(){}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式
        
        $view->display('cms/panel/Sport.tpl');
        return true;
    }
}
?>