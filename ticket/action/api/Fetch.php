<?php
/**
*
*   @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*   All rights reserved
*
*   file:           Fetch.php
*   description:    抢票api
*
*   @author Yuri
*   @license Apache v2 License
*
**/

/**
* 抢票页
*/
class Action_Api_Fetch extends Action_Base
{

    function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function run()
    {
        $resource = $this->getResource();
        $service = new Service_Fetch($resource);

        $ret = array(
                'errno'  => '0',
                'errmsg' => 'OK',
                'data' => array()
                );
        try {
            $data = $service->getTicket($resource['actID']);
            $ret['data'] = $data;
        } catch (Exception $e) {
            $ret = array(
                'errno'  => $e->getCode(),
                'errmsg' =>$e->getMessage()
                );
        }

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}

?>
