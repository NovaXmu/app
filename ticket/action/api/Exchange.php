<?php
/**
*
*    @copyright  Copyright (c) 2015 Nili
*    All rights reserved
*
*    file:            Exchange.php
*    description:     抢票兑换api
*
*    @author Nili
*    @license Apache v2 License
*    
**/

/**
*
*/
/*
class Action_Api_Exchange extends Action_Base
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
            $data = $service->exchange();
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
