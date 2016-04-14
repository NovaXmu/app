<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Person.php
*   description:      Action_Api for Person.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Action_Api_Person extends Action_Base{
    function __construct(){}

    public function run(){
        $m = Library_Share::getRequest('m');
        if(is_bool($m) && !$m){
            $return = array('errno' => '1', 'errmsg' => '参数不对');
        }else{
            switch($m){
            case 'similarRate':
                $return = $this->similarRate();
                break;
            case 'updateUserLabel':
                $return = $this->updateUserLabel();
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

    private function similarRate(){
        $user1 = Library_Share::getRequest('user_ybid');
        $result = Service_User::similarRate($user1, $_SESSION['yb_user_info']['yb_userid']);
        if(is_int($result)){
            switch($result){
                case 1:
                    $ret = array('errno' => '1', 'errmsg' => array('rate' => '没有数据不能比较哦'));
                    break;
                case 2:
                    $ret = array('errno' => '1', 'errmsg' => array('rate' => '不能和自己比较哦'));
                    break;
                case 3:
                    $ret = array('errno' => '1', 'errmsg' => array('rate' => '相似度获取失败'));
                    break;
            }
        }else{
            $ret = array('errno' => '0', 'errmsg' => array('rate' => "相似度$result[0]%"));
        }

        $ret['errmsg']['info'] = Data_User::getUserInfo($user1);

        return $ret;
    }

    private function updateUserLabel(){
        $result = Service_User::updateUserLabel();
        if(is_int($result)){
            switch($result){
                case 1:
                    $ret = array('errno' => '1', 'errmsg' => '数据不全');
                    break;
                case 2:
                case 3:
                    $ret = array('errno' => '1', 'errmsg' => '更新失败');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0', 'errmsg' => 'ok');
        return $ret;
    }

}
?>