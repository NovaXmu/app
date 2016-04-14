<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Push.php
*    description:     推送平台面板
*
*    @author Yuri
*    @license Apache v2 License
*
**/


/**
*  用户信息面板
*/
class Action_Board_Panel_Push
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式


        Vera_Autoload::changeApp('wechat');
        $list = Library_List::getRecent();
        Vera_Autoload::reverseApp();
        $view->assign('pushCount',count($list));

        $view->assign('change',$_SESSION['level'] >= 9 ? 1:0);//只有9权限以上才可以操作
        $view->display('cms/panel/Push.tpl');
        return true;
    }
}

?>
