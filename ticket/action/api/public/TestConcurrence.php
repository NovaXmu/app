<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/25
 * Time: 19:45
 * File: TestConcurrence.php
 * Description:测试抢票接口,临时用
 */

class Action_Api_Public_TestConcurrence
{
    function run()
    {
        $db = Vera_Database::getInstance();
        $id = rand(1,8193);
        $user = $db->select('User', array('id', 'wechat_id', 'real_name', 'college', 'mobile_phone'), array('id' => $id));
        if (empty($user)) {
            exit();
        }
        $_SESSION['user_id'] = $user[0]['id'];
        $_SESSION['openid'] = $user[0]['wechat_id'];
        $_SESSION['user_name'] = $user[0]['real_name'];
        $_SESSION['user_college'] = $user[0]['college'];
//        header('location: /templates/ticket/dist/index.html');
    }
}