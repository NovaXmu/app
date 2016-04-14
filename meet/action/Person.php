<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Person.php
*   description:      Action for Person.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Action_Person extends Action_Base{

    function __construct($resource){
        parent::__construct($resource);
    }

    public function run(){
        $m = Library_Share::getRequest('m');
        if(is_bool($m)){
            return false;
        }
        switch($m){
            case 'index'://test pass
                $this->index();
                break;
            case 'card'://test pass
                $this->card();
                break;
            case 'label'://test pass
                $this->label();
                break;
            case 'circle'://test pass
                $this->circle();
                break;
            case 'PK'://test pass
                $this->PK();
                break;
        }
        return true;
    }

    private function index(){
        $user_ybid = Library_Share::getRequest('user_ybid');
        if(is_bool($user_ybid)){
            $user_ybid = $_SESSION['yb_user_info']['yb_userid'];
        }

        $info = Data_User::getUserInfo($user_ybid);
        // echo 'index info:<br/>';
        // var_dump($info);
        // echo '<br/><br/>';

        $view = new Vera_View(true);//开启dubug模式
        $view->assign('info', $info);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        $view->display('meet/Main.tpl');
        return true;
    }

    private function card(){

        $user_ybid = Library_Share::getRequest('user_ybid');
        if(is_bool($user_ybid)){
            $user_ybid = $_SESSION['yb_user_info']['yb_userid'];
        }

        if($user_ybid == $_SESSION['yb_user_info']['yb_userid']){
            $isMe = true;
        }else{
            $isMe = false;
        }

        $info = Data_User::getUserInfo($user_ybid);

        $list = Service_User::getUserLabelLog($user_ybid);
        // echo 'card list:<br/>';
        //var_dump($list);
        // echo '<br/><br/>';

        $view = new Vera_View(true);
        $view->assign('info', $info);
        $view->assign('list', $list);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        $view->assign('isMe', $isMe);
        $view->display('meet/Card.tpl');
        return true;
    }

    private function label(){

        $data = Service_User::getLabelList();
        // echo 'label data:<br/>';
        // var_dump($data);
        // echo '<br/><br/>';

        $view = new Vera_View(true);
        $view->assign('list', $data['list']);
        $view->assign('type', $data['type']);
        $view->assign('title', $data['title']);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        $view->display('meet/Label.tpl');
        return true;
    }

    private function circle(){

        $list = Service_User::circle();

        // echo 'circle list:<br/>';
        //var_dump($list);
        // echo '<br/><br/>';
        $view = new Vera_View(true);
        $view->assign('list', $list);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        $view->display('meet/Circle.tpl');
        return true;
    }

    private function PK(){

        $me = $_SESSION['yb_user_info']['yb_userid'];
        $he = Library_Share::getRequest('user_ybid');
        if(is_bool($he) || $me == $he){
            return false;
        }

        $result = Data_User::PK($me, $he);

        // echo 'PK result:<br/>';
        // var_dump($result);
        // echo '<br/><br/>';

        $view = new Vera_View(true);
        $view->assign('result', $result);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        $view->display('meet/PK.tpl');
        return true;
    }

}
?>