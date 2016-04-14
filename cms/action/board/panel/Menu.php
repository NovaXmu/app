<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Menu.php
*    description:     自定义菜单面板
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  自定义菜单面板
*/
class Action_Board_Panel_Menu
{

    function __construct()
    {

    }

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $view->display('cms/panel/Menu.tpl');
        return true;
    }
}

?>
