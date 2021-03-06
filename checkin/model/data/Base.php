<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			base.php
*	description:	Data基类
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* Model基类，负责通用的用户信息获取
* 如果其他app调用此data层，构造函数只需要$resource['ID']也就是用户ID。
*/
class Data_Base
{
	private static $resource = NULL;
	private static $userInfo = NULL;

	function __construct($resource)
	{
		self::$resource = $resource;
	}

	protected function getResource()
	{
		return self::$resource;
	}

	protected function setResource($_resource)
	{
		if(empty($_resource))
			throw new Exception("Resource can not be empty", 1);
		self::$resource = $_resource;
	}

	protected static function getUserInfo()
	{
		if (empty(self::$resource) || !isset(self::$resource['num']))
			throw new Exception("Missing arg in resource", 1);

		if(!$db = Vera_Database::getInstance())
			throw new Exception("Cannot get instance of database", 1);

		$result = $db->select('User', '*', array('xmuId' => self::$resource['num']));
		if (!$result) {
			self::$userInfo = -1;
			return false;
		}
		if(isset($result[0]) && !empty($result[0]['yibanUid'])){
            $yiban = $db->select('Yiban', '*', array('uid' => $result[0]['yibanUid']));
            $result['yiban_accessToken'] = isset($yiban[0]['accessToken'])? $yiban[0]['accessToken']:NULL;
            $result['yiban_refreshToken'] = NULL;
            $result['yiban_expireTime'] = isset($yiban[0]['expireTime'])? $yiban[0]['expireTime']:NULL;
        }
		self::$userInfo = $result[0];
		return $result[0];
	}

	private function _getInfo($info)
	{
		if (self::$userInfo == -1) {
			return false;
		}
		$ret = empty(self::$userInfo) ? self::getUserInfo() : self::$userInfo;
		return $ret[$info];

	}

	protected function getID()
	{
		return $this->_getInfo('id');
	}

	protected function getStuNum()
	{
		return $this->_getInfo('xmuId');
	}

	protected function getStuPass()
	{
		return $this->_getInfo('xmuPassword');
	}

	protected function isLink()
	{
		return $this->_getInfo('isLinkedXmu');
	}

	protected function getYibanUid()
	{
		return $this->_getInfo('yibanUid');
	}

	// protected function getYibanAccess()
	// {
	// 	return $this->_getInfo('yiban_accessToken');
	// }

	// protected function getYibanRefresh()
	// {
	// 	return $this->_getInfo('yiban_refreshToken');
	// }

}
?>
