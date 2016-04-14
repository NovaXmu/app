<?php
/**
*
*   @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*   All rights reserved
*
*   file:           List.php
*   description:    获取抢票活动列表api
*
*   @author Yuri
*   @license Apache v2 License
*
**/

/**
*
*/
class Action_Api_List extends Action_Base
{

    function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function run()
    {
        $service = new Service_Info();

        $ret = array(
            'errno' => 0,
            'errmsg' => 'ok',
            'data' => array()
            );
        try {
            $list = $service->getList();
            $ret['data'] = $list;
        } catch (Exception $e) {
            $ret = array(
                'errno'  => $e->getCode(),
                'errmsg' => $e->getMessage()
                );
        }

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}

?>
