<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Message.php
*   description:      Action_Api for Message.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Action_Api_Message extends Action_Base{
    function __construct(){}

    public function run(){
        $m = Library_Share::getRequest('m');
        if(is_bool($m) && !$m){
            $return = array('errno' => '1', 'errmsg' => '参数不对');
        }else{
            switch($m){
            case 'sendMessage'://test pass
                $return = $this->sendMessage();
                break;
            case 'getMoreMessage':
                $return = $this->getMoreMessage();
                break;
            }
        }

        if(isset($return['errno']) && $return['errno'] == 1){
            $log = Library_Share::getLog(true, $return['errmsg']);
        }else{
            $log = Library_Share::getLog(true);
        }
        $log = json_encode($log, JSON_UNESCAPED_UNICODE);
        Vera_Log::addLog('api', $log);

        echo json_encode($return, JSON_UNESCAPED_UNICODE);
        return true;
    }

/**
 * 发送活动内消息
 *
 * @return boolean 是否成功发送活动消息
 */
    private function sendMessage(){
        $data = Library_Share::getRequest('data', Library_Share::ARRAY_DATA);
        $type = Library_Share::getRequest('type', Library_Share::INT_DATA);
        if(is_bool($type)|| is_bool($data)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Message::sendMessage($data, $type);

        if(!is_bool($result)){
            switch($result){
                case 1:
                    $ret = array('errno' => '1', 'errmsg' => '缺失参数');
                    break;
                case 2:
                    $ret = array('errno' => '1', 'errmsg' => '消息内容不能为空');
                    break;
                case 3:
                    $ret = array('errno' => '1', 'errmsg' => '消息内容长度不为超过50');
                    break;
                case 4:
                    $ret = array('errno' => '1', 'errmsg' => '对象类型错误');
                    break;
                case 5:
                    $ret = array('errno' => '1', 'errmsg' => '您没有参加该活动，不可发送活动消息');
                    break;
                case 6:
                    $ret = array('errno' => '1', 'errmsg' => '您没有加入该部落，不可发送部落消息');
                    break;
                case 7:
                    $ret = array('errno' => '1', 'errmsg' => '发送活动消息失败');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0', 'errmsg' => 'ok');
        return $ret;
    }

    private function getMoreMessage(){
        $type = Library_Share::getRequest('type', Library_Share::INT_DATA);
        if(is_bool($type)){
            $ret = array('errno' => '1', 'errmsg' => 'type 不能为空');
            return $ret;
        }
        $index = Library_Share::getRequest('index', Library_Share::INT_DATA);
        if(is_bool($index)){
            $ret = array('errno' => '1', 'errmsg' => 'index 不能为空');
            return $ret;
        }

        switch($type){
            case 1:
            case 2:
            case 3:
                $obj_id = Library_Share::getRequest('to_id', Library_Share::INT_DATA);
                break;
            case 4:
                $obj_id = $_SESSION['yb_user_info']['yb_userid'];
                break;
            case 5:
                $obj_id = $_SESSION['yb_user_info']['yb_userid'];
                break;
            default:
                return array('errno' => '1', 'errmsg' => '参数不对');
                break;
        }

        if(is_bool($obj_id)){
            return array('errno' => '1', 'errmsg' => '参数不对');
        }

        $data = Service_Message::getMessage($type, $obj_id, $index);

        return $data['list'];
    }
}
?>