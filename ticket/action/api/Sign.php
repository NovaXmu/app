<?php
/**
*
*   @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*   All rights reserved
*
*   file:           Sign.php
*   description:    抢票活动签到api
*
*   @author Yuri
*   @license Apache v2 License
*
**/

/**
*
*/
class Action_Api_Sign extends Action_Base
{

    function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function run()
    {
        $resource = $this->getResource();
        $service = new Service_Ticket($resource);

        $ret = array(
            'errno' => 0,
            'errmsg' => 'ok',
            'data' => array()
            );
        try {
            $data = $service->sign();
            $ret['data'] = $data;
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
