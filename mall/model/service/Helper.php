<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Auction.php
 *	description:	竞价service层
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/

/**
 *
 */
class Service_Helper
{
    public static function getList($type)
    {
        $list = Data_Db::getItemsList("startTime",$type);//获取上架的商品,按上架时间排序，类型1表示获取兑换商品
        $res = array();
        if ($list)
        {
            foreach ($list as $key => $value)
            {
                $each = Service_Helper::getDetail($value['id']);
                if (!isset($each['startTime']) || empty($each['startTime']) ||$each['startTime'] == '0000-00-00 00:00:00')
                {
                    $each['startTime'] = $value['startTime'];
                    if ($value['startTime'] == '0000-00-00 00:00:00' || empty($value['startTime']))
                    {
                        $each['startTime'] = $value['onShelfTime'];
                    }
                }
                $res[] = $each;
            }
        }
        return $res;//返回商品列表
    }

    public static function getDetail($id){
        $cache = Vera_Cache::getInstance();
        $key = "mall_" . $id . "_info" ;
        $detail = $cache->get($key);
        if (!empty($detail) && count($detail) > 3)
        {
            return $detail;
        }
        $detail = Data_Db::getItemDetail($id);
        #获得剩余数量和点击量
        $detail['remainAmount'] = Data_Db::getItemRemainAmount($id,$detail['amount']);
        $detail['itemViewed'] = 0;
        $detail['limitConds'] = json_decode($detail['limitConds'], true);
        $cache->set($key,$detail,time()+3600*24*30);//缓存30天
        return $detail;
    }

    public static function checkLimits($userInfo, $detail)
    {
        if (!$detail['remainAmount'])
        {
            return '商品剩余数量已为0';
        }
        if (isset($detail['endTime']) && $detail['endTime'] < date("Y-m-d H:i:s"))
        {
            return '该商品竞价或兑换已结束';
        }
        if (isset($detail['startTime']) && $detail['startTime'] > date("Y-m-d H:i:s"))
        {
            return '该商品竞价还未开始';
        }
        if ($detail['state'] != 1)
        {
            return '商品已下架';
        }
        if (isset($detail['limitConds']['jy']) && !Data_Db::checkLimits($userInfo['yb_exp'], $detail['limitConds']['jy']))
        {
            return '当前经验 ' . $userInfo['yb_exp'] . ' 不满足限制条件';
        }
        if (!empty($detail['limitConds']['sex']) && $userInfo['yb_sex'] != $detail['limitConds']['sex'])//M or F
        {
            return '性别不满足限制条件';
        }
        if (isset($detail['limitConds']['registTime']) && !Data_Db::checkLimits(strtotime($userInfo['yb_regtime']), $detail['limitConds']['registTime']))
        {
            return '您的注册时间 ' . $userInfo['yb_regtime'] . ' 不满足限制条件';
        }
        if ($detail['type'] == 1)//兑换商品特有的一些条件
        {
            return self::checkLimitsForExchange($userInfo, $detail);
        }
        return '';
    }

    public static function checkLimitsForExchange($userInfo, $detail){
        $count = 0;//同种商品计数
        $kinds = array();//已兑换的商品种类计数
        $logs = Service_Person::getPersonLog($userInfo['yb_userid']);
        $logs = $logs['exchange'];
        if ($logs)
        {
            foreach ($logs as $key => $log) {
                if ($log['itemsID'] == $detail['id'])
                {
                    ++$count;
                }
                $itemDetail = self::getDetail($log['itemsID']);
                if ($itemDetail['state'] == 1)
                {
                    $kinds[$log['itemsID']] = $itemDetail;//同个兑换商品多次兑换只取一条
                }
            }
        }
        if (!empty($detail['limitConds']['amountLimit']) && $detail['limitConds']['amountLimit'] <= $count)//amountLimit要求输入数字
        {
            return '可兑换次数已用完';
        }
        $kindsLimit = Data_Cache::getCache('availableKinds');
        $kindsLimit = empty($kindsLimit) ? 3 : $kindsLimit;

        if (count($kinds) >= $kindsLimit && array_search($detail['id'], array_column($kinds, 'id')) === false)
        {
            $str = '您已兑换商品：';
            foreach ($kinds as $key => $value) {
                $str .= $value['name'] . ' ';
            }
            $str .= '留点给别人吧';
            return $str;
        }
    }
}


















