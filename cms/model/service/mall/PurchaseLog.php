<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			PurchaseLog.php
*	description:	mall，获取商品购买记录，service层 
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 组装数据,学号，姓名，商品名称，领取凭证，申请时间，是否已兑换
*/
class Service_Mall_PurchaseLog
{
	public static function getPurchaseLog($id)
	{
		$purchaseLog = Data_Mall::getPurchaseLog($id);
		if (empty($purchaseLog))
		{
			return $purchaseLog;
		}
		foreach ($purchaseLog as $key => $value) {
			$itemInfo = Data_Mall::getItemByID($id);
			$purchaseLog[$key]['itemName'] = $itemInfo[0]['name'];
			$user = self::getUserNameAndStuNum($value['userID']);
			$purchaseLog[$key]['stuNum'] = $user['stuNum'];
			$purchaseLog[$key]['userName'] = $user['userName'];
		}
		return $purchaseLog;
	}
 
	public static function getUserNameAndStuNum($yibanID)
	{
		$cache = Vera_Cache::getInstance();
		$key = 'yb_user_info_' . $yibanID;
		$userInfo = $cache->get($key);
		if (!empty($userInfo))
		{	
			return array('userName' => $userInfo['yb_realname'], 'stuNum' => $userInfo['yb_studentid']);
		}
		return array('userName' => '', 'stuNum' => Data_Mall::getStuNum($yibanID));
	} 
}
?>