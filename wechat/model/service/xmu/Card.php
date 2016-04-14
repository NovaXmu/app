<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Card.php
*    description:    厦大校园卡功能封装
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*
*/
class Service_Xmu_Card
{
    private static $resource = NULL;

    function __construct($_resource)
    {
        self::$resource = $_resource;
    }

    public function getMoneyLeft()
    {
        $cache = Vera_Cache::getInstance();
        $key = 'wechat_money_'. self::$resource['FromUserName'];
        $info = $cache->get($key);
        
        if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
            $data = new Data_Xmu_Jwc(self::$resource);
            $info = $data->getMoney();
            $cache->add($key, $info, '3600');
        }

        $ret['type'] = 'text';
        if (empty($info))
        {
            $info = 'something unusual happened';
        }
        $ret['data']['Content'] = $info;

        return $ret;
    }
}


?>
