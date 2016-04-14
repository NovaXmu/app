<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Auth.php
*    description:    权限验证
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
* 只针对PC端做验证考虑，包括管理面板和大屏幕
*
*/
class Action_Auth extends Action_Base
{
    // @temp: 网络文化节入口
    static $passList = array(
        'Pay',
        'Api_Pay',
        'Data',
        'Test',
        'Api_Data'
    );

	function __construct() {}

	public static function run()
	{
//        session_start();

        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') { //本地访问无条件通过
           //需要通过 localhost 才可以
           Vera_Log::addNotice('name','localhost');
           return true;
       }
        
        // @temp: 网络文化节抽奖页面无条件通过
        if (ACTION_NAME == 'Luck' || ACTION_NAME == 'Api_Luck' || ACTION_NAME == 'Data' || ACTION_NAME == 'Api_Data' || ACTION_NAME == 'Rank') {
            return true;
        }
        if (in_array(ACTION_NAME, self::$passList) && isset($_SESSION['culture'])) {
            return true;
        }

        if (!isset($_SESSION['num'])) {
            return false;
        }
        $resource = array('num' => $_SESSION['num']);
        parent::setResource($resource);
        Vera_Log::addNotice('name',$_SESSION['num']);
        //http://blog.csdn.net/cityice/article/details/9427035
        //session文件锁的问题导致并发长轮询页面挂起，并附带整个网站无响应
        session_write_close();
		switch (ACTION_NAME) {
            case 'Api_Token':
            case 'Api_Act':
                if (!isset($_GET['m']) || !isset($_GET['act'])) {
                    return false;
                }
                return true;
                break;
            case 'Index':
                if (!isset($_GET['act'])) {
                    return false;
                }
                return true;
                break;
            default:
                break;
        }
        return true;
	}

}
?>
