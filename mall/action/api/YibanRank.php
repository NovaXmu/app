<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Date: 2016/1/23
 * Time: 22:44
 */

class Action_Api_YibanRank
{
    function run()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : 'star';
        switch ($m) {
            case 'star':
                $this->getStarRank();
                return;
            case 'medal':
                $this->getMedalRank();
                return;

        }
        echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'));
    }

    function getStarRank()
    {
        $yb_userid = $_SESSION['yb_user_info']['yb_userid'];
        $service = new Service_YibanRank();
        $starRankData = $service->getStarRankData();
        $cache = Vera_Cache::getInstance();
        foreach ($starRankData as $index => $row) {
            if ($row['yb_userid'] != $yb_userid) {
                $starRankData[$index]['award'] = -1; //今日无奖励
            } else if($cache->get('mall_starBobingTimes_' . $yb_userid)) {
                $starRankData[$index]['award'] = 0; //今日已领取
            } else {
                $starRankData[$index]['award'] = 1; //今日未领取
            }
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $starRankData), JSON_UNESCAPED_UNICODE);
    }

    function getMedalRank()
    {
        $yb_userid = $_SESSION['yb_user_info']['yb_userid'];
        Vera_Autoload::changeApp('yiban');
        $publicGroup = Data_Yiban::getPublicGroup($_SESSION['yb_user_info']['access_token']);
        $groupIds = array_column($publicGroup['public_group'], 'group_id');
        Vera_Autoload::reverseApp();

        $service = new Service_YibanRank();
        $medalRankData = $service->getMedalRankData();
        $cache = Vera_Cache::getInstance();
        foreach ($medalRankData as $index => $row) {
            if (!in_array($row['group_id'], $groupIds)) {
                $medalRankData[$index]['award'] = -1; //今日无奖励
            } else if($cache->get('mall_medalBobingTimes_' . $yb_userid . '_' . $row['group_id'])) {
                $medalRankData[$index]['award'] = 0; //今日已领取
            } else {
                $medalRankData[$index]['award'] = 1; //今日未领取
            }
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $medalRankData), JSON_UNESCAPED_UNICODE);
    }
}