<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Event.php
*    description:     事件消息面板
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  事件消息面板
*/
class Action_Board_Panel_Event
{

    function __construct()
    {

    }

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $view->display('cms/panel/Event.tpl');
        return true;
    }
}

?>
