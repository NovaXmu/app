<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Date: 2016/1/31
 * Time: 16:01
 */

class Service_Earn
{
    function getRemainBobingTimes()
    {
        $data = new Data_Db();
        $dailyTimes = Data_Cache::getCache('bobingDailyTimes');
        $dailyTimes = empty($dailyTimes) ? 5 : $dailyTimes;//每天默认五次博饼机会

        $totalGroupBobingTimes = Data_Cache::getCache('totalGroupBobingTimes_' . $_SESSION['yb_user_info']['yb_userid']);
        $starBobingTimes = Data_Cache::getCache('starBobingTimes_' . $_SESSION['yb_user_info']['yb_userid']);
        $xmuMailBobingTimes = Data_Cache::getCache('xmuMailBobingTimes_' . $_SESSION['yb_user_info']['yb_userid'] );
        $times = $dailyTimes - $data->getRemainTimes($_SESSION['yb_user_info']['yb_userid']) + $totalGroupBobingTimes + $starBobingTimes + $xmuMailBobingTimes;
        return $times;
    }
}