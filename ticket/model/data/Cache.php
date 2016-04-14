<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/19
 * Time: 22:09
 * File: Cache.php
 * Description:抢票缓存相关操作
 */

class Data_Cache
{
    public $cache;
    function __construct()
    {
        $this->cache = Vera_Cache::getInstance();
    }

    function getActDetail($actID)
    {
        $key = 'ticket_' . $actID;
        return $this->cache->get($key);
    }

    function setActDetail($actID, $actDetail)
    {
        $key = 'ticket_' . $actID;
        return $this->cache->set($key, $actDetail, time() + 86400); //缓存一天
    }


}