<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			User.php
*	description:	 通用用户信息获取
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 负责通用的用户信息获取
*/
class Data_User
{
	private static $resource = NULL;
	private static $userInfo = NULL;
	private static $yibanInfo = NULL;

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
			return false;

		self::$resource = $_resource;
	}

	protected static function getUserInfo()
	{
		if (empty(self::$resource)) {
			return false;
		}

		if(!$db = Vera_Database::getInstance()) {
			return false;
		}

		$result = $db->select('User', '*', array('wechatOpenid' => self::$resource['FromUserName']));
		if (!$result) {
			self::$userInfo = -1;
			return false;
		}
		self::$userInfo = $result[0];
		return $result[0];
	}

	private static function _getInfo($info)
	{
		if (self::$userInfo == -1) {
			return false;
		}
		$ret = empty(self::$userInfo) ? self::getUserInfo() : self::$userInfo;
		return $ret[$info];

	}

	protected function getYibanInfo()
	{
		if (empty(self::$resource)) {
			return false;
		}

		if(!$db = Vera_Database::getInstance()) {
			return false;
		}

		$num = $this->getStuNum();
		$result = $db->select('Yiban', 'uid,accessToken access_token, expireTime expire_time', array('xmuId' => $num));

		if (!$result) {
			self::$yibanInfo = -1;
			return false;
		}
		self::$yibanInfo = $result[0];
		return $result[0];
	}

	private function _getYibanInfo($info)
	{
		if (self::$yibanInfo == -1) {
			return false;
		}
		$ret = empty(self::$yibanInfo) ? self::getYibanInfo() : self::$yibanInfo;
		return $ret[$info];
	}

	public function getID()
	{
		return $this->_getInfo('ID');
	}

	public function getStuNum()
	{
		return $this->_getInfo('xmuId');
	}

	public function getStuPass()
	{
		return $this->_getInfo('xmuPassword');
	}

	public function isLink()
	{
		return $this->_getInfo('isLinkedXmu');
	}

	public function getYibanUid()
	{
		return $this->_getInfo('yibanUid');
	}

	public function getYibanAccess()
	{
		return $this->_getYibanInfo('access_token');
	}

	public function getYibanExpire()
	{
		return $this->_getYibanInfo('expire_time');
	}

}
?>
