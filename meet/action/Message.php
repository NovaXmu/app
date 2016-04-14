<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Message.php
*   description:      Action for Message.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/

class Action_Message{
    function __construct(){}

    public function run(){
        $m = Library_Share::getRequest('m');
        if(is_bool($m)){
            return false;
        }
        switch($m){
            case 'index'://test pass
                $this->index();
                break;
            case 'readMessage':
                $this->readMessage();
                break;
        }
        return true;
    }

    private function index(){

        $list = Service_Message::getMessageMenu();

        // echo 'index list MessageMenu:<br/>';
        // var_dump($list);

        $view = new Vera_View(true);
        $view->assign('list', $list);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        $view->display('meet/MessageMenu.tpl');
        return true;
    }

    private function readMessage(){
        $type = Library_Share::getRequest('type', Library_Share::INT_DATA);
        if(is_bool($type)){
            return false;
        }
        $index = Library_Share::getRequest('index', Library_Share::INT_DATA);
        if(is_bool($index)){
            $index = 0;
        }
        $setRead = Library_Share::getRequest('setRead', Library_Share::INT_DATA);
        if(is_bool($setRead)){
            $setRead = -1;
        }

        switch($type){
            case 1://查看悄悄话
                $obj_id = Library_Share::getRequest('user_ybid', Library_Share::INT_DATA);
                break;
            case 2://查看活动消息 test pass
                $obj_id = Library_Share::getRequest('activity_id', Library_Share::INT_DATA);
                break;
            case 3://查看部落消息 test pass
                $obj_id = Library_Share::getRequest('blog_id', Library_Share::INT_DATA);
                break;
            case 4://查看PK消息
                $obj_id = $_SESSION['yb_user_info']['yb_userid'];
                break;
            case 5://查看话题消息
                $obj_id = $_SESSION['yb_user_info']['yb_userid'];
                break;
            default:
                return false;
                break;
        }

        if(is_bool($obj_id)){
            return false;
        }

        $data = Service_Message::getMessage($type, $obj_id, $index, $setRead);

        //var_dump($data);

        $view = new Vera_View(true);//开启dubug模式
        $view->assign('ret', $data['ret']);//是否有权限查看消息(errno errmsg)
        $view->assign('title', $data['title']);
        $view->assign('type', $data['type']);
        $view->assign('return_id', $data['return_id']);
        $view->assign('to_id', $data['to_id']);
        $view->assign('list', $data['list']);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        if($type == 1 || $type == 2 || $type == 3)
            $view->display('meet/Message.tpl');
        else
            $view->display('meet/MessageDetails.tpl');
        return true;
    }
}
?>