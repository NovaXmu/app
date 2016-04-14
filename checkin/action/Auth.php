<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Auth.php
*    description:    权威性验证action
*
*    @author Yuri
*    @license Apache v2 License
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
            case 'Test':
                return true;
            case 'Index':
                return self::_index();
                break;
            case 'Api_Answer':
                return self::_answer();
                break;
            case 'Api_Question':
                return self::_question();
                break;
            case 'Api_Pay':
                return self::_apiPay();
            case 'Pay':
                return self::_pay();
            case 'Api_Extendpay':
                return self::_extendPay();
            default:
                return true;
                break;
        }
	}

    private static function _index()
    {
        self::_checkXmuNum();
        return true;
    }

    private static function _answer()
    {
        self::_checkXmuNum();
        //校验Get参数
        if (!isset($_GET['id']) || !isset($_GET['answer']) || !is_numeric($_GET['id'])) {
            $ret = array('errno' => '6001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        return true;
    }

    private static function _question()
    {
        self::_checkXmuNum();
        if(!is_numeric($_SESSION['xmu_num'])){
            $ret = array('errno' => '6001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        return true;
    }

    private static function _apiPay(){
        self::_checkXmuNum();
        if(!is_numeric($_SESSION['xmu_num'])){
            $ret = array('errno' => '6001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        return true;
    }

    private static function _pay(){
        self::_checkXmuNum();
        if(!is_numeric($_SESSION['xmu_num'])){
           return false;
        }
        return true;
    }

    private static function _extendPay(){
        self::_checkXmuNum();
        if(!is_numeric($_SESSION['xmu_num'])){
            $ret = array('errno' => '6001', 'errmsg' => '参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        return true;
    }

    private static function _checkXmuNum(){
        if(!isset($_SESSION['xmu_num']) || !is_numeric($_SESSION['xmu_num'])){
            if(!isset($_SESSION['openid']))
                self::_getWechatCode();
            else{
                $_SESSION['xmu_num'] = Data_Db::getXmuNum();
                $resource['num'] = $_SESSION['xmu_num'];
            }
        }else{
            $resource['num'] = $_SESSION['xmu_num'];
        }
        parent::setResource($resource);
        return true;
    }

    private static function _getWechatCode(){
        $result = Vera_Conf::getConf('global');
        $appID = $result['wechat']['AppID'];
        if($_SERVER['HTTP_HOST'] == 'test.novaxmu.cn')
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=http%3a%2f%2ftest.novaxmu.cn%2fyiban%2fentry&response_type=code&scope=snsapi_userinfo&state=checkin#wechat_redirect";
        else
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=http%3a%2f%2fwww.novaxmu.cn%2fyiban%2fentry&response_type=code&scope=snsapi_userinfo&state=checkin#wechat_redirect";
        header('Location:'.$url);
        exit;
    }

    private static function modifyCheckin(){
        $db = Vera_Database::getInstance();
        $list = $db->select('checkin_Log', '*', 'isPay != -1');
        for($i = 0; $i<count($list); $i++){
            $rows = array(
                'money' => $list[$i]['isPay'],
                'isPay' => 1
                );
            $db->update('checkin_Log', $rows, array('id' => $list[$i]['ID']));
        }
        $list = $db->select('checkin_Log', '*', array('isPay' => -1));
        for($i = 0; $i<count($list); $i++){
            $rows = array(
                'money' => $this->getMoney($list[$i]['ID'], $list[$i]['question_ID'])
                );
            $db->update('checkin_Log', $rows, array('id' => $list[$i]['ID']));
        }
    }

    private function getMoney($logId, $questionId){
        $db = Vera_Database::getInstance();
        $conds = "ID <= $logId and question_ID = $questionId";
        $count = $db->selectCount('checkin_Log',$conds);
        //可领取网薪数量
        switch($count){
                case 1:
                    return 50;
                case 2:
                case 3:
                case 4:
                case 5:
                    return 20;
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                    return 10;
                default:
                    return 5;
        }
    }
}
?>
