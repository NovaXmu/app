<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Checkin.php
*    description:     签到平台面板
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  签到平台面板
*/
class Action_Board_Panel_Checkin
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $view->caching = Smarty::CACHING_OFF;
        $view->display('cms/panel/Checkin.tpl');
        return true;
    }
}

?>
