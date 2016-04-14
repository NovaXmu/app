<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Auction.php
*	description:	兑换service层
*
*	@author Nili
*	@license Apache v2 License
*
**/

/**
*
*/
class Service_Exchange
{
	public function exchange($userInfo,$id){
		$ret = array('errno' => 0,'errmsg' => 'ok','token' => array());
		$detail = Service_Helper::getDetail($id);
		if ($userInfo['yb_money'] < $detail['price'])//网薪余额不够
		{
			$ret = array('errno' => 1,'errmsg' => '网薪余额不足','token' => array());
			return $ret;
		}
		$tem = Service_Helper::checkLimits($userInfo, $detail);
		if ($tem)//判断满不满足限制条件
		{
			$ret = array('errno' => 1,'errmsg' => $tem,'token' => array());
			return $ret;
		}
		Vera_Autoload::changeApp('yiban');
		if (!Data_Yiban::paySalary($userInfo['yb_userid'],$userInfo['access_token'], $detail['price']))
		{
			$ret = array('errno' => 1,'errmsg' => '支付网薪失败，请重新购买','token' => array());
			return $ret;
		}
		Vera_Autoload::reverseApp();
		$token = Data_Db::createToken();
		if (!Data_Db::exchange($userInfo['yb_userid'], $id, $detail['price'], $token))//易班id，物品id，物品价格，凭证号
		{//这种情况基本没有吧，数据库里没录入这条信息
			$ret = array('errno' => 0,'errmsg' => '凭证号录入数据库失败，凭截图找工作人员兑换商品','token' => array());
		}
		$ret['token'] = $token;
		$detail['remainAmount'] --;
		if (!$detail['remainAmount'])//剩余数量为0了
		{
			Data_Db::setEndTime($id);//更新商品详情表里的endTime
		}
		$cache = Vera_Cache::getInstance();
		$key = "mall_" . $id ."_info";
		$cache->set($key, $detail, date("Y-m-d H:i:s")+3600*24*30);
		return $ret;
	}
}
?>
