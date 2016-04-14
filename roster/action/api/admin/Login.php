<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Login.php
*   description:      后台登录
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Api_Admin_Login{
    public function run(){

        $account = Library_Share::getRequest('account');
        $password = Library_Share::getRequest('password');

        if (is_bool($account) || is_bool($password)) {
            Vera_Log::addVisitLog('res', '参数错误');
            echo json_encode(array('errno' => 1, 'errmsg' => '参数错误'), JSON_UNESCAPED_UNICODE);
            return false;
        }


        $db = new Data_Db();
        $ret = $db->isManager($account, $password);
        if (empty($ret)) {
            Vera_Log::addVisitLog('res', '非法请求');
            echo json_encode(array('errno' => 1, 'errmsg' => '非法请求'), JSON_UNESCAPED_UNICODE);
            return;
        }
        switch($ret[0]['privilege']){
            case '1':
                $ret[0]['job'] = '超级管理员';
                break;
            case '2':
                $ret[0]['job'] = '项目管理员';
                break;
            default:
                break;
        }
        $_SESSION['manager'] = $ret[0];

        Vera_Log::addVisitLog('res', 'ok');

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'));
        return true;
    }
}
?>