<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			SuperAdmin.php
*	description:	超级管理员登陆操作
*
*	@author Nili
*	@license Apache v2 License
*
**/

class Action_Api_Public_SuperAdmin
{
    function run ()
    {
        $this->Login();
    }
    function Login()
    {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            Vera_Log::addVisitLog('res', '参数错误');
            echo json_encode(array('errno' => 1, 'errmsg' => '参数错误'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        $pwd = Vera_Cache::getInstance()->get('cargo_root');

        $pwd = empty($pwd) ? md5('rootroot') : $pwd;
        if (empty($pwd) || $pwd != md5($username . $password)) {
            Vera_Log::addVisitLog('res', '用户名或密码错误');
            echo json_encode(array('errno' => 1, 'errmsg' => '用户名或密码错误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $_SESSION['user_id'] = -1;
        $_SESSION['isAdmin'] = true;
        Vera_Log::addVisitLog('res', 'ok');
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return;
    }
}