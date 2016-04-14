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

	function __construct() {}

	public static function run()
	{
		switch (ACTION_NAME) {
            case 'Linkin':
            case 'Api_Unlink':
                return self::_linkin();
                break;
            case 'Api_Xmulinkin':
                return self::_xmuLinkin();
                break;
            case 'Api_Yibanlinkin':
                return self::_yibanLinkin();
                break;
            case 'Together':
                return self::_together();
                break;
            case 'Sport':
                return true;//积分榜页面任何人可看
            case 'Api_Sport':
                return self::_sport();//只有关注者可点赞或留言
                break;
            default:
                return true;
                break;
        }
	}

    private static function _linkin()
    {
        if (!isset($_GET['openid'])) {
            return false;
        }
        $resource['openid'] = $_GET['openid'];
        parent::setResource($resource);
        return true;
    }

    private static function _xmuLinkin()
    {
        if (!isset($_POST['num']) || !isset($_POST['password']) || !isset($_POST['openid'])) {
            $ret = array('errno' => '3001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $resource['num'] = $_POST['num'];
        $resource['password'] = $_POST['password'];
        $resource['openid'] = $_POST['openid'];
        parent::setResource($resource);
        return true;
    }

    private static function _yibanLinkin()
    { 
//        session_start();
        if (isset($_SESSION['openid']))
        {
            return true;
        }
       
        if (isset($_GET['openid']))
        {
             $_SESSION['openid'] = $_GET['openid'];
             $resource['openid'] = $_GET['openid'];
             parent::setResource($resource);
             return true;
        }
        header("Location:http://q.yiban.cn/app/index/appid/1530");
        return false;
    }

    /**
     * 同路人权限验证
     * @return bool 验证结果
     */
    private static function _together()
    {
//        session_start();
        //登录过后直接放行
        if(isset($_SESSION["yb_user_info"]) && !empty($_SESSION['yb_user_info']) && $_SESSION['yb_user_info']['token_expires'] >= date("Y-m-d H:i:s")) {
            return true;
        }

        header("Location: /yiban/EntryFromYiban?appName=wap/together?m=update");
        exit();
    }

    /**
    * 校运会积分榜入口
    * @author linjun
    */
    private static function _sport(){
//        session_start();
        if(isset($_SESSION['openid']) && !empty($_SESSION['openid'])){
            return true;
        }

        //$_SESSION['openid'] = isset($_GET['openid'])?$_GET['openid'] : 'echo';
        return true;
    }

}
?>
