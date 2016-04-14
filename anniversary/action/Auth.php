<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
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
    private static $message = array(
        'public' => array('index', 'getMoreMessage', 'addMessage', 'info'),
        'auditer' => array('audit', 'addAuditer', 'setAuditer', 'getMessageList', 'auditMessage')
        );

    public static function run(){
        if(ACTION_NAME == 'test'){
            return true;
        }
        if(strpos(ACTION_NAME, 'Message') !== false){
            return self::checkMessage();
        }
        return true;
    }

    private static function checkMessage(){
        if(!isset($_GET['m']) || empty($_GET['m'])){
            $m = 'index';
        }else{
            $m = $_GET['m'];
        }
        //公共
        if(in_array($m, self::$message['public'])){
            return true;
        }
        //审核
        if(in_array($m, self::$message['auditer'])){
            if(isset($_SESSION['isAuditer']) && $_SESSION['isAuditer'] === true){
                return true;
            }else{
                if(self::_isAuditer()){
                    $_SESSION['isAuditer'] = true;
                    return true;
                }
            }
        }
        header("location:/anniversary/message");
        exit;
    }

    private static function _isAuditer(){
        if(!isset($_SESSION['openid']) || empty($_SESSION['openid'])){
            if(is_bool(Library_Share::getRequest('openid')))
                self::_getWechatCode();
            else
                $_SESSION['openid'] = Library_Share::getRequest('openid');
        }
        $db = new Data_User();
        return $db->isAuditerByOpenid($_SESSION['openid']);
    }

    private static function _getWechatCode(){
        $result = Vera_Conf::getConf('global');
        $appID = $result['wechat']['AppID'];
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=http%3a%2f%2ftest.novaxmu.cn%2fyiban%2fentry&response_type=code&scope=snsapi_userinfo&state=anniversary#wechat_redirect";
        header('Location:'.$url);
        exit;
    }
}
?>