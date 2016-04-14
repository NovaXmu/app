<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Date: 2016/1/31
 * Time: 16:07
 */

class Action_Api_Public_XmuMail
{
    function run()
    {
        if (!isset($_GET['m']) || empty($_GET['m'])) {
            return;
        }
        switch ($_GET['m']) {
            case 'verifyCode':
                $this->verifyCode();
                break;
            default:
                # code...
                break;
        }
    }

    function verifyCode()
    {
        if (!isset($_GET['code']) || empty($_GET['code']) || !isset($_GET['yb_userid'])) {
            return;
        }
        $cache = Vera_Cache::getInstance();

        $yb_userid = $cache->get($_GET['code']);
        if (!$yb_userid || $yb_userid != $_GET['yb_userid']) {
            echo '非法令牌';
            return ;
        }

        if ($cache->get('mall_xmuMailBobingTimes_' . $yb_userid)) {
            echo '今日已领取过该奖励';
            return ;
        }

        $cache->set('mall_xmuMailBobingTimes_' . $yb_userid, 5, strtotime('tomorrow'));//每日五次额外博饼次数奖励
        echo '领取成功';
    }
}