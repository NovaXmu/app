<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Linkin.php
*   description:      Action for Linkin.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Checkin{
    public function run(){
        if(!isset($_SESSION['token']) || empty($_SESSION['token']))
            return false;
        $arr = explode('|', $_SESSION['token']);
        $service = new Service_Wechat();
        $log = $service->checkIn($arr[0], $arr[1]);
        $view = new Vera_View(true);
        $view->assign('course', $log['course']);
        $view->assign('message', $log['message']);
        $view->display('roster/wechat/checkin.tpl');
        return true;
    }
}

?>