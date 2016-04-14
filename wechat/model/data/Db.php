<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Db.php
*	description:	数据库相关操作
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 			
*/
class Data_Db
{
	/**
	 * 检查身份绑定情况
	 * @param  string $openid 微信openid
	 * @return array         code:不同层次的绑定，0都没绑定，1只绑定厦大身份，2只绑定易班，3都绑定
	 *         				wechat_id,xmu_num,xmu_isLinked,yiban_uid,yiban_isLinked
	 */
	public static function checkLinkin($openid)
	{
		$db = Vera_Database::getInstance();
		$res = $db->select('User', 'wechatOpenid wechat_id, xmuId xmu_num,isLinkedXmu xmu_isLinked, yibanUid yiban_uid, isLinkedYiban yiban_isLinked',"wechatOpenid='{$openid}'");
		$res = $res[0];
		$ret = 0;
		if ($res['xmu_isLinked'])
		{
			$ret += 1;
		}
		if ($res['yiban_isLinked'])
		{
			$ret += 2;
		}
		return array_merge($res, array('code' =>$ret));
	}

	/**
	 * 根据易班id获取有效accessToken
	 * @param   $yiban_uid 易班id
	 * @return string accessToken，若无，返回''
	 * @author nili 
	 */
	public static function getValidAccessToken($yiban_uid)
	{
		$db = Vera_Database::getInstance();
		$res = $db->select('Yiban', 'accessToken access_token, expireTime expire_time'), array('uid' => $yiban_uid));
		if (empty($res) || $res[0]['expire_time'] < date("Y-m-d H:i:s"))
		{
			return '';
		}
		return $res[0]['access_token'];
	}

	/**
	 * 查看某用户今天是否已有抽奖记录
	 * @param   $yiban_uid 易班id
	 * @return  int 记录数
	 * @author nili 
	 */
	public static function getTodayLog($yiban_uid)
	{
		$db = Vera_Database::getInstance();
		$res = $db->select('wechat_TmpLuck', 'count(*)', "yiban_uid={$yiban_uid} AND time>'" . date("Y-m-d 00:00:00") . "'");
		return $res[0]['count(*)'];
	}

	/**
	 * 插入一条抽奖日志
	 * @param string $yiban_uid 易班id
	 * @param string $xmu_num 学号
	 * @param string $award 获得网薪值
	 * @return  int 影响行数
	 * @author nili 
	 */
	public static function insertLog($yiban_uid, $xmu_num, $award)
	{
		$db = Vera_Database::getInstance();
		return $db->insert('wechat_TmpLuck', 
			array('xmu_num' => $xmu_num, 
			'yiban_uid' => $yiban_uid, 
			'award' => $award, 
			'time' => date("Y-m-d H:i:s")));
	}
}

?>