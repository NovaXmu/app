<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Func.php
*	description:	
*
*	@author Nili
*	@license Apache v2 License
*	
**/
/**
* 		
*/
class Service_Func	 
{

	/*
	*保存用户实名信息
	*存缓存，key为'yb_user_info_'. $ybUserid
	*存数据库，vera_User
	*存SESSION
	*/
	public static function saveRealInfo ($userRealInfo,$tokenAndExpires)
	{
		$realInfoWithToken = array_merge($userRealInfo, $tokenAndExpires);

		$data = new Data_Db();
		$data->insertUserInfo($realInfoWithToken);//insert yb_user_info into Yiban
		//if ($realInfoWithToken['yb_userid'] == '1596251')
			//Vera_Log::addLog('tmp', date('Y-m-d H:i:s') . ' ' . Vera_Database::getLastSql());
		$data->setYibanUid($realInfoWithToken);//set yibanUid in User

		$cache = Vera_Cache::getInstance();
		$key = 'yb_user_info_' . $realInfoWithToken['yb_userid'];
		$cache->set($key,$realInfoWithToken,time() + 3600*24*30);

		$_SESSION['yb_user_info'] = $realInfoWithToken;
	}

	/**
	 * 获取实名信息
	 * @param $access_token
	 * @return array 实名信息
	 * @author nili 
	 */
	public static function getRealInfo($access_token)
	{
		$user = new Library_Ybapi_User($access_token);
		$realInfo = $user->realme();
		if ($realInfo['status'] == 'success')
		{
			return $realInfo['info'];
		}
		return array();
	}
}