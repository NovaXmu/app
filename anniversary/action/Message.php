<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Message.php
*   description:      Action for Message.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Message{
    public function run(){
        if(!isset($_GET['m']) || empty($_GET['m'])){
            $m = 'index';
        }else{
            $m = $_GET['m'];
        }

        switch($m){
            case 'index'://留言地图，不需要登录
                $this->index();
                break;
            case 'info'://校友会信息，不需要登录
                $this->info();
                break;
            case 'audit'://留言审核,需要openid,并且是后台管理人员 test pass
                $this->audit();
                break;
        }
        return true;
    }

    private function index(){
        $db = new Data_User();
        $worldLoc = $db->getLocationList();
        $chinaLoc = $db->getLocationList(array('country' => 'China'));
        $service = new Service_Message();
        $messageList = $service->getMessageList(true, 1, 1);
        $db = new Data_Message();
        $count = $db->getMessageCount();
        $view = new Vera_View(true);
        // var_dump($chinaLoc);
        // var_dump($worldLoc);
        $view->assign('count', $count);
        $view->assign('worldLoc', $worldLoc);
        $view->assign('chinaLoc', $chinaLoc);
        $view->assign('messageList', $messageList);
        $view->display('anniversary/message/index.tpl');
        return true;
    }

    private function info(){
        $view = new Vera_View(true);
        $view->display('anniversary/message/info.tpl');
        return true;
    }

    private function audit(){
        $service = new Service_Message();
        $list = $service->getMessageList(false, 0);
        $view = new Vera_View(true);
        $view->assign('list', $list);
        $view->display('anniversary/message/audit.tpl');
        return true;
    }
}
?>