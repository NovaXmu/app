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
class Action_Super{
    function run(){
        if(!isset($_GET['m']) || empty($_GET['m'])){
            return false;
        }

        switch($_GET['m']){
            case 'managerList':
                $this->managerList();
                break;
        }
    }

    private function managerList(){
        $db = new Data_Db();
        $view = new Vera_View(true);
        //$view->assign('page', ceil($db->getManagerCount()/10));
        $view->assign('list', $db->getManagerList(1));
        $view->display('roster/admin/managerList.tpl');
        return true;
    }
}
?>