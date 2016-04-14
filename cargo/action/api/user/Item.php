<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Item.php
*	description:	Item.php,普通用户获取item列表等
*
*	@author Nili
*	@license Apache v2 License
*
**/

class Action_Api_User_Item extends Action_Base
{
    public function run ()
    {
        $this->getItem();
    }

    function getItem()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $data = new Data_Db();
            $res = $data->getItem(array('id' => $_GET['id'], 'deleted' => 0));
            $res = $res[0];
        } else {
            $service = new Service_Item();
            $res = $service->getAllItemsWithCategory();
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