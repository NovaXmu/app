<?php
/**
*	@copyright
*	
*	file:		Vote.php
*	description:投票平台信息操作类
*
*	@author linjun
*/

/**
*	投票平台信息操作类
*/
class Data_Vote{

	function __construct(){}

	/**
	* 获取待审核的活动列表
	* @return array
	*/
	public function getNeedReviewActs(){
		$db = Vera_Database::getInstance();
		$conds = array('isPassed' => 0);
		$append = 'order by startTime asc';
		$result = $db->select('voteAct', '*', $conds, $append);

		return $result;
	}	

	/**
	* 设置审核结果
	* @param int $actID 
	* @param int $isPassed -1未通过 0尚未决定 1 审核通过
	*/
	public function setPass($actID, $isPassed = 1){
		$db = Vera_Database::getInstance();
		$row = array('isPassed' => $isPassed);
		$conds = array('actID' => $actID);
		$db->update('voteAct', $row, $cond);

		return true;
	}

	/**
	* 获取待开始的活动列表
	* @return array
	*/
	public function getReadActs(){
		$db = Vera_Database::getInstance();
		$now = date('Y-m-d H:i:s',time());
		$cond= "isPassed = 1 and startTime > '{$now}' ";
		$apped = 'order by startTime asc';
		$result = $db->select('voteAct', '*', $cond, $append);
		if(!$result){
			return false;
		}
		return $result;
	}

	/**
	* 获取正在进行的活动列表
	* @return array
	*/
	public function getNowActs(){
		$db = Vera_Database::getInstance();
		$now = date('Y-m-d H:i:s', time());
		$cond = "isPassed = 1 and startTime <= '{$now}' and endTime > '{$now}'";
		$result = $db->select('voteAct', '*', $cond);
		if(!$result){
			return false;
		}
		return $result;
	}

	/**
	* 获取正常结束(isPassed = 1)或者未通过审核(isPassed = -1)的活动列表
	* @param int $isPassed
	* @return array
	*/
	public function getEndActs($isPassed){
		$db = Vera_Database::getInstance();

		$append = NULL;
		if($isPassed == 1){
			$now = date('Y-m-d H:i:s', time());
			$cond = "isPassed = 1 and endTime <= '{$now}'";
			$append = 'order by actID desc';
		}
		else{
			$cond = array('isPassed' => -1);
			$append = 'order by actID desc';
		}

		$result = $db->select('voteAct', '*', $cond, $append);

		if(!$result){
			return false;
		}

		return $result;
	}

	/**
	* 获取活动详情
	* @param $actID
	* @return array
	*/
	public function getActInfo($actID){
		$db = Vera_Database::getInstance();
		$cond = array('actID' => $actID);
		$ret = $db->select('voteAct', '*', $cond);
		if(!$ret){
			return false;
		}

		if($ret['isPassed'] == 1){//如果投票通过审核已经开始或者已经结束则显示投票结果
			$count = this->_getCount($actID);//获取参与人数
			$result = this->_getResult($actID);//获取投票前五名
			$ret['count']  = $cout;
			$ret['result'] = $result;
		}

		return $ret;
	}

	/**
	* 获取项目详情
	* @param int $proID
	* @return array 
	*/
	public function getProInfo($proID){
		$db = Vera_Database::getInstance();
		$cond = array('proID' => $proID);
		$ret = $db->select('votePro', '*', $cond);
		if(!$ret){
			return false;
		}
		retur $ret;
	}

	/**
	* 添加投票活动
	* @param varchar $actName
	* @param string  $actImg 图片url
	* @param varchar $actContent
	* @param string  $startTime
	* @param string  $endTime
	* @param array   $conditions
	*/
	public static function addAct($actName, $actImg, $actContent, $startTime, $endTime, $conditions = NULL){
		$db = Vera_Database::getInstance();
		$rows = array(
			'actName' => $actName,
			'actImg' => $actImg,
			'actContent' => $actContent,
			'startTime' => $startTime,
			'endTime' => $endTime,
			'conditions' => $conditions
			);
		$result = $db->insert('voteAct',$rows);
		if(!$result){
			return false;
		}
		return $result;
	}

	/**
	* 添加投票项目
	* @param int $actID
	* @param string $proName
	* @param string $proImg
	* @param string $proContent
	*/
	public static function addPro($actID, $proName, $proImg, $proContent){
		$db = Vera_Database::getInstance();

		$rows = array(
			'proName' => $proName,
			'proImg' => $proImg,
			'proContent' => $proContent,
			'actID' => $actID,
			'poll' => 0
			);
		$ret = $db->insert('votePro',$rows);
		if(!$ret){
			return false;
		}
		return $ret
	}

	/**
	* 统计参与人数
	* @param int $actID
	* @return int 
	*/
	public function _getCount($actID){
		$db = Vera_Database::getInstance();
		$cond = array('actID' => $actID);
		$ret = $db->selectCount('voteLog', '*', $cond);
		if(!$ret){
			return false;
		}
		return $ret;
	}

	/**
	* 获取投票结果
	* @param int $actID
	* @return array
	*/
	public function _getResult($actID){
		$db = Vera_Database::getInstance();

		$cond = array('actID' => $actID);
		$append = 'order by poll desc limit 0,5';

		$ret = $db->select('votePro', '*', $cond, $append);
		if(!$ret){
			return false;
		}
		return $ret;
	}

}
?>