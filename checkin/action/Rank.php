<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            List.php
*    description:    签到排行榜
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
* 签到排行榜
*/
class Action_Rank extends Action_Base
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式
        $view->setCacheLifetime( -1 );//缓存时间设为永不过期，通过cache_id控制缓存时间

        if (isset($_GET["mode"]) && $_GET["mode"] == 'lastMonth') {
            $cacheID = "last|".date('m');//缓存每月重新生成一次
            $isCached = $view->isCached('checkin/Rank.tpl', $cacheID);
            Vera_Log::addNotice('isCached', intval($isCached));

            if (!$isCached) {
                $view->clearCache('checkin/Rank.tpl', 'last');//清除旧缓存

                $model = new Data_Db();
                $data = $model->monthRank('last');

                $view->assign('title','上月排行榜');
                $view->assign('data',$data);//data是一个数组，每一项的num是学号，count是累计签到次数

            }
            $view->dailyBackground();
            $view->display('checkin/Rank.tpl',$cacheID);
        }
        else {
            $cacheID = "this|".date('d');//每天重新生成
            $isCached = $view->isCached('checkin/Rank.tpl',$cacheID);
            Vera_Log::addNotice('isCached', intval($isCached));

            if (!$isCached) {
                $view->clearCache('checkin/Rank.tpl', 'this');//清除旧缓存

                $model = new Data_Db();
                $data = $model->monthRank('this');//默认显示本月排行榜

                $view->assign('title','本月排行榜');
                $view->assign('data',$data);


                $view->assign('centerColor', '#b2f4eb');
                $view->assign('borderColor', '#2365c7');
            }
            $view->dailyBackground();
            $view->display('checkin/Rank.tpl',$cacheID);
        }
    }
}

?>
