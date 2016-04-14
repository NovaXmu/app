<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Linkin.php
*   description:      身份绑定
*
*   @author Echo
*   @license Apache v2 License
*
**/
/**
* 身份绑定
*/
class Action_Api_Linkin
{
    
    function run()
    {
        if (!isset($_GET['openid']) || empty($_GET['openid']) || !isset($_GET['telephone']) || empty($_GET['telephone'])) {
            Vera_Log::addVisitLog('res', '参数有误');   
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $db = new Data_Db();
        $user = $db->getUser(array('telephone' => $_GET['telephone']));
        if (empty($user)) {
            Vera_Log::addVisitLog('res', '用户信息不存在，无法绑定');   
            echo json_encode(array('errno' => 1, 'errmsg' => '用户信息不存在，无法绑定'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $db->setUser(array('id' => $user['id']), array('openid' => $_GET['openid']));
        $user['openid'] = $_GET['openid'];
        Vera_Log::addVisitLog('res', 'ok');
        $_SESSION['user'] = $user;
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return;
    }

}
?>