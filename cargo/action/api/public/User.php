<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			User.php
*	description:	User.php,查询身份
*
*	@author Nili
*	@license Apache v2 License
*
**/
class Action_Api_Public_User
{
    function run ()
    {
        $this->getUser();
    }
    function getUser()
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '身份未绑定'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $data = new Data_Db();
        $admin = $data->getPrivilege(array('user_id' => $_SESSION['user_id'], 'deleted' => 0));
        if (!empty($admin)) {
            $_SESSION['isAdmin'] = 1;
            Vera_Log::addVisitLog('res', '管理员');
            echo json_encode(array('errno' => 0, 'errmsg' => '管理员'), JSON_UNESCAPED_UNICODE);
            return;
        } else if ($_SESSION['user_id'] == -1) {
            $_SESSION['isAdmin'] = 1;
            Vera_Log::addVisitLog('res', '超级管理员');
            echo json_encode(array('errno' => 0, 'errmsg' => '超级管理员'), JSON_UNESCAPED_UNICODE);
            return;
        } else {
            Vera_Log::addVisitLog('res', '普通用户');
            echo json_encode(array('errno' => 0, 'errmsg' => '普通用户'), JSON_UNESCAPED_UNICODE);
            return;
        }
    }
}