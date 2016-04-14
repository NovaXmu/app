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
        if (strpos(ACTION_NAME, 'Public') !== false) {
            return true;
        }

        if (empty($_SESSION['user_id']) || empty($_SESSION['user_xmuNum'])) {
            self::setSession();
        }

        if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
            return true;//管理员拥有最高权限，任何接口都可以访问
        } else if (strpos(ACTION_NAME, 'Admin') !== false) {
            echo json_encode(array('errno' => 1, 'errmsg' => '非法请求'), JSON_UNESCAPED_UNICODE);
            return false;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SESSION['user_name']) || empty($_SESSION['user_xmuNum']))) {
            echo json_encode(array('errno' => 1, 'errmsg' => '信息不完善，不能进行该操作'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        return true;
    }

    public static function setSession()
    {
        if (empty($_SESSION['openid'])) {
            //跳转至微信授权
            $conf = Vera_Conf::getConf('global');
            $conf = $conf['wechat'];
            $appID = $conf['AppID'];
            $redirectUrl = "http://{$_SERVER['HTTP_HOST']}/yiban/entry";
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=$redirectUrl&response_type=code&scope=snsapi_base&state=ticket#wechat_redirect*/
";
            header("location: $url");
            exit();
        }

        if (empty($_SESSION['user_id']) || empty($_SESSION['user_xmuNum'])) {
            $data = new Data_Db();
            $user = $data->getUser($_SESSION['openid']);
            if (empty($user[0])) {
                //跳转至绑定页面
                header('location: /templates/ticket/dist/linkin.html');
//                echo json_encode(array('errno' => 1, 'errmsg' => '该用户尚未绑定厦大账号'), JSON_UNESCAPED_UNICODE);
                exit();
            }

            self::setAdminSession($_SESSION['openid']);

            $_SESSION['user_id'] = $user[0]['id'];
            $_SESSION['user_name'] = $user[0]['realName'];
            $_SESSION['user_xmuNum'] = $user[0]['xmuId'];
        }
    }

    public static function setAdminSession($openid)
    {
        $data = new Data_Db();
        $adminId = $data->getAdminId($openid);
        if ($adminId) {
            $_SESSION['admin_id'] = $adminId;
        }
    }
}
    /*
    private static function _index()
    {
        if (!isset($_GET['actID']) || !is_numeric($_GET['actID'])) {
            return false;
        }
        $resource['openid'] = isset($_GET['openid'])? $_GET['openid'] : '';
        $resource['actID'] = intval($_GET['actID']);
        parent::setResource($resource);
        //self::_auth('ticket/api/fetch?actID='.$resource['actID'].'&openid='.$resource['openid']);
        return true;
    }

    private static function _fetch()
    {
        if (!isset($_GET['openid']) || !isset($_GET['actID']) || !is_numeric($_GET['actID'])) {
            $ret = array('errno' => '4001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $resource['openid'] = $_GET['openid'];
        $resource['actID'] = intval($_GET['actID']);
        parent::setResource($resource);
        //$ret = self::_auth('ticket/index?actID='.$resource['actID'].'&openid='.$resource['openid']);
        //var_dump($ret);
        // if($ret){
        //     return true;
        // }else{
        //     return false;
        // }
        return true;
    }

    private static function _sign()
    {
        if (!isset($_POST['openid']) || !isset($_POST['token']) || !is_numeric($_POST['token']) || !isset($_POST['actID']) || !is_numeric($_POST['actID'])) {
            $ret = array('errno' => '4001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $resource['openid'] = $_POST['openid'];
        $resource['token'] = intval($_POST['token']);
        $resource['actID'] = intval($_POST['actID']);
        parent::setResource($resource);
        //self::_auth('ticket/index?actID='.$resource['actID'].'&openid='.$resource['openid']);
        return true;
    }

    private static function _Exchange()
    {
        if (!isset($_POST['token']) || !is_numeric($_POST['token']) || !isset($_POST['actID']) || !is_numeric($_POST['actID'])) {
            $ret = array('errno' => '4001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $resource['token'] = intval($_POST['token']);
        $resource['actID'] = intval($_POST['actID']);
        parent::setResource($resource);
        return true;
    }

    private static function _auth($where){
        $db = new Data_Base(parent::getResource());
        $ret = $db->isLinkYb();
        if(!$ret){
            var_dump('isLinkYb:',$ret);
            header("Location: /yiban/EntryFromYiban?appName=".$where);
            exit();
        }else{
            var_dump('isLinkYb:',$ret);
            return true;
        }
    }

}
?>*/
