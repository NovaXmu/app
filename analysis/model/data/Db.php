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
	 * 根据易班id获取有效易班accessToken
	 * @param  string $yibanId 易班id
	 * @return string          有效accessToken
	 * @author nili
	 */
	public function getAccessToken($yibanId)
	{
		$db = Vera_Database::getInstance();
		$where = "uid=$yibanId AND expireTime > '" . date('Y-m-d H:i:s') . "'";
		$res = $db->select('Yiban', 'accessToken', $where);
		return empty($res) ? null : $res[0]['accessToken'];
	}

	public function insertWxLog($adminId, $yb_uid, $award, $reason, $res)
	{
		$db = Vera_Database::getInstance();
		$insert = array(
			'adminId' => $adminId,
			'yb_uid' => $yb_uid,
			'award' => $award,
			'reason' => $reason,
			'result' => $res,
			'time' => date('Y-m-d H:i:s')
		);
		$db->insert('analysis_WxLog', $insert);
	}
}

?>