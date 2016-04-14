<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            User.php
*    description:     用户信息面板
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  用户信息面板
*/
class Action_Board_Panel_User
{

    function __construct()
    {

    }

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式
        $data = new Data_User();

        $result = $data->getUserList(20);
        $count = $result['count'];
        $users = array_slice($result['users'], -10);
        $users = array_reverse($users);

        $view->assign('users', $users);
        $view->assign('count', $count);
        unset($result['users']); 
        $view->assign('linkCount', $result);
        $view->caching = Smarty::CACHING_OFF;
        $view->display('cms/panel/User.tpl');
        return true;
    }
}


?>
