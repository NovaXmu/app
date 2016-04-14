<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Auth.php
*   description:      Action for Auth.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Auth{

    //public权限
    private static $publicList = array(
        'Admin',
        'Index',
        'Api_Admin_Login',
        'Api_Linkin'
        );

    //项目管理员权限
    private static $projectAdminList = array(
        'Project',
        'Api_Admin_Project'
        );

    //超级管理员权限
    private static $superAdminList = array(
        'Super',
        'Api_Admin_Super'
        );

    private static $wechatList = array(
        'Wechat',
        'Api_Wechat',
        'Checkin'
        );


    public static function run(){
//        session_start();

        //公共,不需要验证权限
        if(in_array(ACTION_NAME, self::$publicList)){
            return true;
        }

        //项目管理员,验证
        if(in_array(ACTION_NAME, self::$projectAdminList)){
            if(isset($_SESSION['manager']) && !empty($_SESSION['manager']) && $_SESSION['manager']['privilege'] == '2'){
                return true;
            }
            Vera_Log::addVisitLog('res', '非法请求');
            if(strpos(ACTION_NAME, 'Api_') === 0)
                echo json_encode(array('errno'=>1,'errmsg'=>'请先登录'), JSON_UNESCAPED_UNICODE);
            else
                header('Location:/roster/admin?m=login');
            exit();
        }

        //超级管理员,验证
        if(in_array(ACTION_NAME, self::$superAdminList)){
            if(isset($_SESSION['manager']) && !empty($_SESSION['manager']) && $_SESSION['manager']['privilege'] == '1'){
                return true;
            }
            Vera_Log::addVisitLog('res', '非法请求');
            if(strpos(ACTION_NAME, 'Api_') === 0)
                echo json_encode(array('errno'=>1,'errmsg'=>'请先登录'), JSON_UNESCAPED_UNICODE);
            else
                header('Location:/roster/admin?m=login');
            exit();
        }

        //微信端验证
        if(in_array(ACTION_NAME, self::$wechatList)){
            if(isset($_GET['token']) && !empty($_GET['token']))
                $_SESSION['token'] = $_GET['token'];
            if(isset($_SESSION['user']) && !empty($_SESSION['user'])){
                return true;
            }
            if(!isset($_SESSION['openid']) && empty($_SESSION['openid'])){
                if(is_bool(Library_Share::getRequest('openid')))
                    self::_getWechatCode();
                else
                    $_SESSION['openid'] = Library_Share::getRequest('openid');
            }
            if(isset($_GET['m']) && $_GET['m'] == 'linkin'){
                return true;
            }
            $db = new Data_Db();
            $user = $db->getUser(array('openid' => $_SESSION['openid']));
            if(empty($user)){
                header('Location:/roster/wechat?m=linkin');//跳转到绑定页面
                exit();
            }
            $_SESSION['user'] = $user;
            return true;
        }

         header('Location:/roster/index');
         exit();
    }

    private static function _getWechatCode(){
        $result = Vera_Conf::getConf('global');
        $appID = $result['wechat']['AppID'];
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=http%3a%2f%2ftest.novaxmu.cn%2fyiban%2fentry&response_type=code&scope=snsapi_base&state=roster/".ACTION_NAME."#wechat_redirect";
        header('Location:'.$url);
        exit();
    }
}
?>