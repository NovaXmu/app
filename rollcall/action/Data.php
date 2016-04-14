<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Data.php
*   description:      大屏幕实时数据页面(临时)
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

/**
* @temp:
*/
class Action_Data extends Action_Base
{
    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $view->display('rollcall/Data.tpl');
        return true;
    }
}

?>
