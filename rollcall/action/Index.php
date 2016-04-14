<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Board.php
*    description:     大屏幕面板Websocket服务端入口
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

class Action_Index extends Action_Base
{
    function __construct() {}

    public function run()
    {
        $resource = $this->getResource();
        $act = $_GET['act']; // 通过加密过的token选择对应的板
        $num = $resource['num'];
        if (!Service_Info::isActBelong($act, $num) || !Service_Info::isActPassed($act)){
            Vera_Log::addWarning('unknown act');
            return false;
        }
        $info = Service_Info::getActInfo($act);

        $view = new Vera_View(true);
        $view->assign('act', $act);
        $view->assign('info', $info);
        $view->display('rollcall/Board.tpl');
    }
}
?>
