<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Wechat.php
*   description:      Action for Wechat.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Api_Wechat{
    function run(){
        if(!isset($_GET['m']) || empty($_GET['m']))
            return false;

        switch($_GET['m']){
            case 'startCheck':
                $this->startCheck();
                break;
        }
        return true;
    }

    private function startCheck(){
        set_time_limit(30);//防止出现异常导致PHP超时从而引发前端无响应。
        $service = new Service_Wechat();
        $result = $service->startCheck();
        $ret = array('errno'=>1, 'errmsg'=>'错误');
        if(is_bool($result)){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
?>