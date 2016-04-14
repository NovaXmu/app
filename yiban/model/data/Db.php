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
	public static function insertWechatUser($userInfo){
		$db = Vera_Database::getInstance();
		$data = array(
			'city' => $userInfo['city'],
			'country' => $userInfo['country'],
			'province' => $userInfo['province'],
			);
		$update = $data;
		$data['openid'] = $userInfo['openid'];
		$userRows = array(
			'wechatNickname' => $userInfo['nickname'],
			'sex' => $userInfo['sex'] == 1 ? '男' : '女'
			);
		$up = $userRows;
		$userRows['wechatOpenid'] = $userInfo['openid'];
		$db->insert('User', $userRows, NULL, $up);
		return $db->insert('Wechat', $data, NULL, $update);
	}
	
	/**
	*在vera_Yiban表里插入用户信息
	* @param  array          $userInfo       用户信息，各种
	* @return int            影响行数
	* @author linjun         done
	*/
	public static function insertUserInfo($userInfo){
	     $db = Vera_Database::getInstance();
	     $data = array(
	          // 'xmuId' => $userInfo['yb_studentid'],
	          'accessToken' => $userInfo['access_token'],
	          'expireTime' => $userInfo['token_expires']);
	     $update = $data;
	     $data['uid'] = $userInfo['yb_userid'];
	     $insert = $data;
	     //利用MySQL特性 ON DUPLICATE KEY UPDATE，当违反uid的unique时，使用update
	    $userInfo['yb_sex'] = $userInfo['yb_sex'] == 'F' ? '女' : '男';
		$rows = array(
	     	'yibanNickname' => $userInfo['yb_usernick'],
	     	'realname' => $userInfo['yb_realname'],
	     	'sex' => $userInfo['yb_sex'],
	     	'school' => $userInfo['yb_schoolname'],
	     	'identity' => $userInfo['yb_identity']
	     	);
	     $up = $rows;
	     $rows['yibanUid'] = $userInfo['yb_userid'];
	     $ret = $db->insert('User', $rows, NULL, $up);
	     return $db->insert('Yiban', $insert, NULL, $update);
	}

	/**
	*在vera_User表里插入用户的yiban_uid,根据学号插入
	* @param  array          $userInfo       
	* @return int            影响行数
	* @author linjun   test pass
	*/
	public static function setYibanUid($userInfo){
	     $db = Vera_Database::getInstance();
	     $conds = array('xmuId' => $userInfo['yb_studentid']);
	     $rows = array('yibanUid' => $userInfo['yb_userid']);
	     $result = $db -> update('User', $rows, $conds, NULL, NULL);
	}

}
?>