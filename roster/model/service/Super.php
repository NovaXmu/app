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
class Service_Super{
    function __construct(){}

    public function getManagerList(){
        $page = Library_Share::getRequest('page', Library_Share::INT_DATA);
        if(is_bool($page)){
            return false;
        }
        $db = new Data_Db();
        return $db->getManagerList($page);
    }

    public function addManager(){
        $manager = array();
        $manager['account'] = Library_Share::getRequest('account');
        $manager['nickname'] = Library_Share::getRequest('nickname');
        $manager['password'] = Library_Share::getRequest('password');
        $manager['privilege'] = Library_Share::getRequest('privilege', Library_Share::INT_DATA);
        if(is_bool($manager['account']) || is_bool($manager['nickname']) || is_bool($manager['password']) || is_bool($manager['privilege'])){
            return false;
        }
        $db = new Data_Db();
        return $db->addManager($manager);
    }

    public function useManager($use){
        $managerId = Library_Share::getRequest('id', Library_Share::INT_DATA);
        if(is_bool($managerId))
            return false;
        if($use)
            $arr = array('isUse' => 1);
        else
            $arr = array('isUse' => -1);
        $db = new Data_Db();
        return $db->modifyManager($managerId, $arr);
    }
}
?>