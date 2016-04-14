<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Pay.php
*   description:      Action for Pay.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Pay extends Action_Base
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);
        $resource = $this->getResource();
        $db = new Data_Db($resource);
        $num = $resource['num'];
        $count = $db->isPayForExtend();
        if($count == 0){
            $isPay = false;
            $title = '答题记录';
            $log = $db->getCheckinLog(1);
        }
        else{
            $isPay = true;
            $title = '补领网薪';
            $log = $db->getCheckinLog(-1);
        }
        $view->assign('isPay', $isPay);
        $view->assign('title', $title);
        $view->assign('count', $log['count']);
        $view->assign('money', $log['money']);
        $view->assign('num', $resource['num'], true);//动态赋值学号
        $view->assign('centerColor', '#b2f4eb');
        $view->assign('borderColor', '#2365c7');
        $view->dailyBackground();
        $view->display('checkin/Pay.tpl');
        return true;
    }
}
?>