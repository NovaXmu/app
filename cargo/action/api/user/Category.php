<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Category.php
*	description:	Category.php,普通用户获取分类信息
*
*	@author Nili
*	@license Apache v2 License
*
**/
class Action_Api_User_Category
{
    function run ()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : 'take';
        switch ($m) {
            case 'take':
                $this->getCategory(0);
                break;
            case 'borrow':
                $this->getCategory(1);
                break;
            default:
                echo json_encode(array('errno' =>1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
                break;
        }
    }

    function getCategory($type)
    {
        $data = new Data_Db();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $res = $data->getCategory(array('id' => $_GET['id'], 'type' => $type, 'deleted' => 0));
            $res = $res[0];
        } else {
            $res = $data->getCategory(array('type' => $type, 'deleted' => 0));
        }
        $res = array(
            'errno' => 0,
            'errmsg' => 'ok',
            'data' => $res
        );
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        return;

    }
}