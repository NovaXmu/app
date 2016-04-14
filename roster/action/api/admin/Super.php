<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Super.php
*   description:      Action for Super.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Api_Admin_Super{
    public function run(){
        if(!isset($_GET['m']) || empty($_GET['m'])){
            return false;
        }

        switch($_GET['m']){
            case 'getManagerList'://pass
                return $this->getManagerList();
                break;
            case 'getManagerPage'://pass
                return $this->getManagerPage();
                break;
            case 'addManager'://pass
                return $this->addManager();
                break;
            case 'useManager'://pass
                return $this->useManager(true);
                break;
            case 'unuseManager'://pass
                return $this->useManager(false);
                break;
        }
    }

    private function getManagerList(){
        $service = new Service_Super();
        $result = $service->getManagerList();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(is_bool($result)){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function getManagerPage(){
        $db = new Data_Db();
        $count = $db->getManagerCount();
        if(is_int($count) && $count != 0){
            $ret = array('errno' => 0, 'errmsg'=>ceil($count/10));
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return true;
        }else{
            $ret = array('errno' => 0, 'errmsg'=>0);
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return true;
        }
    }

    private function addManager(){
        $service = new Service_Super();
        $result = $service->addManager();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(!$result){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function useManager($use){
        $service = new Service_Super();
        $result = $service->useManager($use);
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(!$result){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
?>