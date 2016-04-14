<?php
/**
* @copyright
*
* file: Base.php
* description: Data基类
*
* @author linjun
*/
class Data_Base{
	private static $resource = NULL;
	private static $userInfo = NULL;
	private static $actInfo = NULL;

	function __construct($resource){
		self::$resource = $resource;
	}

	protected function getResource(){
		return self::$resource;
	}

	protected function setResource($resource){
		if(empty($resource)){
			throw new Exception("Resource cannot be empty", 1);
		}

		self::$resource = $resource;
	}

	protected static function getUserInfo(){
		if(empty(self::$resource))
			throw new Exception('Resource cannot be empty',1);

		if(!$db = Vera_Database::getInstance())
			throw new Exception('Cannot get instance of database', 1);

		if (isset(self::$resource['openid'])) {
			$result = $db->select('vera_User', '*', array('wechat_id' => self::$resource['openid']));
		}
		else {
			$result = $db->select('vera_User', '*', array('id' => self::$resource['id']));
		}

		if (!$result) {
			self::$userInfo = -1;
			return false;
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
		return $this->_getInfo('xmu_num');
	}

	protected function getStuPass()
	{
		return $this->_getInfo('xmu_password');
	}

	protected function isLink()
	{
		return $this->_getInfo('xmu_isLinked');
	}

	protected function getYibanUid()
	{
		return $this->_getInfo('yiban_uid');
	}

	protected function getYibanAccess()
	{
		return $this->_getInfo('yiban_accessToken');
	}

	protected function getYibanRefresh()
	{
		return $this->_getInfo('yiban_refreshToken');
	}

}
?>