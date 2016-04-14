<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Admin.php
*    description:     平台管理面板
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  平台管理面板
*/
class Action_Board_Panel_Admin
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式
        $data = new Data_Admin();

        $admins = $data->getList();
        $view->assign('users',$admins);
        $view->assign('currentLevel',$_SESSION['level']);

        $view->caching = Smarty::CACHING_OFF;
        $view->display('cms/panel/Admin.tpl');
        return true;
    }
}

?>
