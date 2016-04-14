<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Admin.php
*   description:      管理员登录
*
*   @author Echo
*   @license Apache v2 License
*
**/
Class Action_Admin{

    public function run(){
        //登录 不需要检查是否有登录
        if(!isset($_GET['m']) || empty($_GET['m'])){
            $this->login();
            return true;
        }

        switch($_GET['m']){
            case 'login':
                $this->login();
                break;
            case 'index':
                $this->index();
                break;
        }
        return true;
    }

    private function login(){
        if(isset($_SESSION['manager'])){
            unset($_SESSION['manager']);
        }
        $view = new Vera_View(true);
        $view->display('roster/admin/login.tpl');
        return true;
    }


    private function index(){
        if(isset($_SESSION['manager']) && !empty($_SESSION['manager'])){
            $view = new Vera_View(true);
            $view->assign('user', $_SESSION['manager']);
            $view->display('roster/admin/index.tpl');
            return true;
        }else{
            header('Location:/roster/admin?m=login');
        }
    }

}
?>