<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Category.php
*	description:	Category.php,管理员操作分类，新增，修改，删除等
*
*	@author Nili
*	@license Apache v2 License
*
**/

class Action_Api_Admin_Category
{

    function run ()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : 'add';
        switch ($m) {
            case 'add':
                $msg = $this->addCategory();
                break;
            case 'modify':
                $msg = $this->modifyCategory();
                break;
            default:
                $msg = "非法m";
        }
        if ($msg) {
            echo json_encode(array('errno' => 1, 'errmsg' => $msg), JSON_UNESCAPED_UNICODE);
        }
    }

    function addCategory ()
    {
        if (!isset($_POST['name']) || !isset($_POST['type']) || !is_numeric($_POST['type'])) {
            return '参数有误';
        }

        $type = $_POST['type'];
        $data = new Data_Db();
        $id = $data->setCategory(array(
            'name' => $_POST['name'],
            'time' => date('Y-m-d H:i:s'),
            'type' => $type,
            'admin_id' => $_SESSION['user_id']
        ));
        if (!$id) {
            return '未知原因新增失败';
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $id), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function modifyCategory()
    {
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            return '参数有误';
        }
        $info = array();
        if (isset($_POST['deleted'])) {
            $info['deleted'] = 1;
        }

        if (isset($_POST['name']) && !empty($_POST['name'])) {
            $info['name'] = $_POST['name'];
        }

        if (empty($info)) {
            return '参数有误';
        }
        $data = new Data_Db;
        $data->setCategory($info, $_POST['id']);
        if (isset($info['deleted'])) {
            $data->deleteItemByCategory($_POST['id']);
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return '';
    }
}