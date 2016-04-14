<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Admin.php
*    description:     管理员数据表操作封装
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  数据表操作
*/
class Data_Admin
{
    function __construct() {}

    public function getInfo($username)
    {
        $db = Vera_Database::getInstance();
        $cond = array('user'=>$username, 'isPassed' => 1);
        $result = $db->select('cms_Admin', '*', $cond);
        if ($result) {
            return $result[0];
        }
        else {
            return false;
        }
    }

    public function getList()
    {
        $db = Vera_Database::getInstance();
        $result = $db->select('cms_Admin', '*', array('deleted' => 0), NULL, 'order by level desc');
        if ($result) {
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * 根据管理员id获取管理员可见的栏目
     * @param $uid
     * @return bool|结果数组
     * @author nili 
     */
    public static function getAdminMenu($uid)
    {
        $db = Vera_Database::getInstance();
        return $db->select('cms_Privilege', "privilege", array('uid' => $uid, 'level' => 1,'deleted' => 0));
    }

    /**
     * 获取当前配置文件中所配置的所有权限
     * @return array 权限信息，menus和subPrivileges
     * @author nili 
     */
    public function getAllPrivileges()
    {
        $conf = Vera_Conf::getAppConf('authority');
        $board = $conf['Board']['Panel'];
        foreach($board as $app => $description)
        {
            $menu[$app] = $description['name'];
        }
        $subPrivileges = $conf['SubPrivilege'];
        unset($subPrivileges['comment']);
        foreach($subPrivileges as $app => $apis) {
            foreach($apis as $api => $ms) {
                foreach ($ms as $m => $description){
                    $sub[$app . '/' . $api . '/' . $m] = $description;
                }
            }
        }
        return array('menus' => $menu, 'subPrivileges' => $sub);
    }

    /**
     * 根据管理员id获取管理员信息
     * @param  int $id 管理员id
     * @return array     管理员信息
     * @author nili 
     */
    public function getAdminById($id)
    {
        $db = Vera_Database::getInstance();
        $cond = array('id'=>$id, 'deleted' => 0);
        $result = $db->select('cms_Admin', '*', $cond);
        if ($result) {
            return $result[0];
        }
        else {
            return array();
        }
    }
}

?>
