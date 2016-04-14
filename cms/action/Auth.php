<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			Auth.php
*	description:	权威性验证action
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 权威性验证
*/
class Action_Auth extends Action_Base
{
    static $passList = array(//无条件通过列表
        'Index',
        'Api_Login'
        );

	function __construct() {}

	public static function run()
	{
//        session_start();
        if (in_array(ACTION_NAME, self::$passList)) {//无条件通过
            return true;
        }

        if (strpos(ACTION_NAME, 'Public')) {
            return true;
        }
        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') { //本地访问无条件通过
           //需要通过 localhost 才可以
           Vera_Log::addNotice('name','localhost');
           return true;
       }
        Vera_Log::addNotice('level',$_SESSION['level']);
        Vera_Log::addNotice('id',$_SESSION['id']);
        if (!isset($_SESSION['level'])) {//若没有登录，则默认赋值0级权限
            $_SESSION['id'] = -1;
            $_SESSION['name'] = 'unknown';
            $_SESSION['level'] = 0;
        }
        if ($_SESSION['level'] == 10){
            return true;
        }
        $tmp = explode("_", ACTION_NAME);
        $app = array_pop($tmp);
        if ($app == 'Index'){
            return true;
        }
        if (strpos(ACTION_NAME, 'Api') !== false && !empty($_GET['m'])){
            $privilegeInDb = "{$app}/Action_Api_{$app}/" . $_GET['m'];
            if (!self::checkPrivilegeInConf($privilegeInDb)){
                return true;
            }
        } else {
            $privilegeInDb = $app;
        }

        $db = Vera_Database::getInstance();
        $res = $db->select('cms_Privilege', '*', array('uid' => $_SESSION['id'], 'privilege' => $privilegeInDb, 'deleted' => 0));
        if (empty($res)){
            echo json_encode(array('errno' => 1, 'errmsg' =>"权限不足！"));
            return false;
        }
        return true;
	}

    /**
     * 查看conf中是否对某api设置访问权限，若设置，返回true，若没有设置，返回false
     * @param $app
     * @param $api
     * @param $m
     * @return bool
     */
    public static function checkPrivilegeInConf($privilege)
    {

        $db = new Data_Admin();
        $privileges = $db->getAllPrivileges()['subPrivileges'];
        if (isset($privileges[$privilege]))
            return true;
        return false;

    }
}

