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
class Service_Auction 
{
	
	function __construct()
	{	
	}
	//检查该商品是否结束竞价，结束的商品则给出价最高者插入token
	public static function checkEndAuctionToken($id)
	{
		$detail = Service_Helper::getDetail($id);
		if ($detail['endTime'] < date("Y-m-d H:i:s"))
		{
			$res = Data_Db::checkAuctionToken($id);//根据商品id检查log表中当前出价最高者有没有token值
			Data_Db::updateToken($res['id'], 'temToken');
			if ($res)
			{
				Vera_Autoload::changeApp('yiban');
				$tem = Data_Yiban::paySalary($res['userID'], Data_Db::getAccessToken($res['userID']), $res['price']);//付网薪
				Vera_Autoload::reverseApp();
				$token = Data_Db::createToken();
				if (!$tem)//付款失败，最高出价者无足够余额
				{
					$token = '余额不足或授权已过期';
				}
				Data_Db::updateToken($res['id'], $token);
				$detail['remainAmount'] --;
				$cache = Vera_Cache::getInstance();
				$key = "mall_" . $id . "_info";
				$cache->set($key, $detail, time()+3600*24*30);
			}
		}
	}

	public function auction($userInfo, $id, $price){
		$ret = array('errno' => 0,'errmsg' => 'ok');
		$detail = Service_Helper::getDetail($id);
		$price += $detail['price'];
		if ($userInfo['yb_money'] < $price) //网薪余额不够
		{
			$ret = array('errno' => 1,'errmsg' => '网薪余额不足');	
			return $ret;	
		}
		$tem = Service_Helper::checkLimits($userInfo, $detail);
		if ($tem)//判断满不满足限制条件
		{
			$ret = array('errno' => 1,'errmsg' => $tem);
			return $ret;	
		}
		Data_Db::auction($userInfo['yb_userid'],$id,$price);//写入数据库
		Data_Db::setPrice($id,$price);//重置商品价格,detail表里只记录当前最高价格
		$cache = Vera_Cache::getInstance();//更新缓存里价格
		$key = "mall_" . $id . "_info" ;
		$detail['price'] = $price;
		$cache->set($key,$detail,time()+3600*24*30);
		return $ret;
	}
}
?>