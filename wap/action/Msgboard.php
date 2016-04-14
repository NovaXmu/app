<?php
/**
*
*   @copyright  Copyright (c) 2015 nidaren
*   All rights reserved
*
*   file:           Auth.php
*   description:    同路人
*
*   @author nidaren
*   @license Apache v2 License
*
**/


class Action_Msgboard extends Action_Base
{
    function __construct (){}

    public function run()
    {
        $cache = Vera_Cache::getInstance();
        $key = 'wap_msgboard_count';
        $count = $cache->get($key);
        if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
            $count = 13203; //转发数起点
        }
        $count = $count + rand(5,30);//每次递增 5~30 不等
        $cache->set($key, $count);
        $view = new Vera_View(true);
        $view->assign('title', '风雨94载,致敬永远的厦大人,母校生日快乐。我是第'.$count.'个致敬者');
        $view->display('wap/Msgboard.tpl');
        return true;
    }
}

?>
