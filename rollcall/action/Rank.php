<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Rank.php
*   description:      @temp: 网络文化节手机端排行榜
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

/**
* @temp:
*/
class Action_Rank extends Action_Base
{
    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $view->display('rollcall/Rank.tpl');
        return true;
    }
}

?>
