<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Wx.php
*	description:	
*
*	@author Nili
*	@license Apache v2 License
*	
**/
/**
* 网薪收发data层
*/
class Data_Wx 
{
	/**
	 * 根据易班id获取易班token
	 * @param  [type] $yibanId [description]
	 * @return [type]          [description]
	 */
	public function getAccessToken($yibanId)
	{
		$db = Vera_Database::getInstance();
		$where = "uid={$yibanId} AND expireTime >'" . date("Y-m-d H:i:s") . "'"; 
		$res = $db->select('Yiban', 'accessToken access_token', $where);
		if (empty($res))
		{
			return false;
		}
		return $res[0]['access_token'];
	}

	/**
	 * 网薪收发插入日志
	 * @param  [type] $adminId [该操作管理员id]
	 * @param  [type] $yb_uid  [description]
	 * @param  [type] $award   [description]
	 * @param  [type] $reason  [description]
	 * @param  [type] $result  [description]
	 * @return [type]          [description]
	 */
	public function insertWxLog($adminId, $yb_uid, $award, $reason, $result)
	{
		$db = Vera_Database::getInstance();
		$insert = array('adminId' => $adminId, 
			'yb_uid' => $yb_uid, 
			'award' => $award, 
			'reason' => $reason, 
			'result' => $result, 
			'time' => date('Y-m-d H:i:s'));
		$db->insert('cms_WxLog', $insert);
	}
}