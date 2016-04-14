<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Person.php
*	description:	个人商城
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Service_Person
{
	
	function __construct(){	
	}

	public static function getPersonLog($userID)
	{
		$personLog = Data_Db::getPersonLog($userID);
		$itemEffectiveDay = Data_Cache::getItemEffectiveDay();
		$data = array('exchange' => array(), 'auction' => array());
		if (!empty($personLog))
		{
			$personLog = self::array_msort($personLog, 'id');//按id降序排，即按时间降序排
			foreach ($personLog as $key => $value) {
				$itemInfo = Service_Helper::getDetail($value['itemsID']);
				$personLog[$key]['info'] = $itemInfo;
				if ($itemInfo['type'])//兑换商品
				{
					$personLog[$key]['itemTokenExpires'] = date("Y-m-d H:i:s", strtotime($value['time']) + $itemEffectiveDay * 3600 * 24);
					$data['exchange'][] = $personLog[$key];
				}
				else
				{
					$personLog[$key]['itemTokenExpires'] = date('Y-m-d H:i:s',strtotime($personLog[$key]['info']['endTime']) + $itemEffectiveDay * 3600 * 24);
					if ($personLog[$key]['info']['endTime'] < date("Y-m-d H:i:s"))
					{
						Service_Auction::checkEndAuctionToken($personLog[$key]['info']['id']);
						$personLog[$key]['info']['isOverdued'] = 1;
					}
					else
					{
						$personLog[$key]['info']['isOverdued'] = 0;
					}
					$data['auction'][] = $personLog[$key];
				}
				
			}
		}
		return $data;

	}

	//二维数组按指定键排序
	public static function array_msort($arr, $key, $type='desc')
	{
		$newArr = $keyValue = array();
		foreach ($arr as $k => $v) {
			$keyValue[$k] = $v[$key];
		}
		if ($type == 'desc')
		{
			arsort($keyValue);
		}
		if ($type == 'asc')
		{
			asort($keyValue);
		}
		reset($keyValue);
		foreach ($keyValue as $k => $v) {
			$newArr[$k] = $arr[$k];
		}
		return $newArr;
	}

	public static function getAward($userID)
	{
		$result = array();

		$result['bobing'] = Data_Db::getTotalAward('bobing', $userID);
		$result['game2048'] = Data_Db::getTotalAward('game2048', $userID);
		return $result;
	}

	public static function getAwardLog($userID)
	{
		$result = array();
		$result['bobing'] =  Data_Db::getYibanAwardLog($userID, 10, 'bobing');
		$result['game2048'] = Data_Db::getYibanAwardLog($userID, 10, 'game2048');
		return $result;
	}
}
?>