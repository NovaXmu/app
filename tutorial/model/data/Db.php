<?php
/**
* @copyright
*
* file: Db.php
* descripton: 投票Data层数据获取封装类
*
* @author linjun
*
* 数据库
* table:voteAct
* attribute: actID actName actImg actContent startTime endTime isPassed conditions
*
* table:votePro
* attribute:proID proName proImg proContents actID poll
*
* table:voteLog
* attribute:id xmu_num yiban_uid proID actID date
*/

class Data_Db extends Data_Base{

	function __construct($resource = NULL){
		parent::__construct($resource);
	}

	/**
	* 检查是否绑定厦大账号
	* @return bool
	*/
	public function checkLink(){
		return $this->isLink();
	}

	/**
	* 获取正在进行的投票活动列表
	* @return array of actID
	*/
	public static function getNowActList(){
		$db = Vera_Database::getInstance();
		$lastDay = date('Y-m-d H:i:s', time());
		$conds = "endTime >= '{$lastDay}' and startTime <= '{$lastDay}' and isPassed = 1 order by startTime asc";
		$ret = $db->select('voteAct', 'actID', $conds);
		if(!$ret){
			return false;
		}
		return $ret;
	}

	/**
	* 获取已经过期的投票活动列表
	* @return array of actID
	*/
	public static function getOverActList(){
		$db = Vera_Database::getInstance();
		$lastDay = date('Y-m-d H:i:s', time());
		$conds = "endTime < '{$lastDay}' and isPassed = 1 order by endTime asc";
		$ret = $db->select('voteAct', 'actID', $conds);
		if(!$ret){
			return false;
		}
		return $ret;
	}

	/**
	* 获取投票活动的介绍
	* @param int $actID
	* @return array 
	*/
	public static function getActInfo($actID){
		$db = Vera_Database::getInstance();
		$conds = array(
			'actID' => $actID,
			'isPassed' => 1
			);
		$ret = $db->select('voteAct', '*', $conds);
		if(!$ret){
			return false;
		}
		return $ret;
	}

	/**
	* 获取投票活动的项目
	* @param array $order 排序：时间(startTime)升序排序 票数(poll)降序排序
	* @param int $actID 投票活动ID
	* @return array of proID
	*/
	public static function getProList($order = 'poll',$actID){
		$db = Vera_Database::getInstance();
		$conds = "actID = '{$actID}' and isPassed = 1 order by '{$order}' desc";
		$ret = $db->select('votePro', 'proID', $conds);
		if(!$ret){
			return false;
		}
		return $ret;
	}

	/**
	* 获取投票项目信息
	* @param int $proID
	* @return array 项目信息
	*/
	public static function getProInfo($proID){
		$db = Vera_Database::getInstance();
		$conds = array('proID'=>$proID);
		$ret = $db->select('votePro', '*', $conds);
		if(!$ret){
			return false;
		}
		return $ret;
	}

	/**
	* 获取投票项目前五
	* @param array $order 排序：时间(startTime)升序排序 票数(poll)降序排序
	* @param int $actID 投票活动ID
	* @return array of proID
	*/
	public static function getHotProList($actID){

		$db = Vera_Database::getInstance();

		$arrhot = array();
		$append = 'order by poll desc';
		$list = $db->select('votePro', '*', NULL, $append);
		$arrHot = array(
			'0' => $list['0']['proID'],
			'1' => $list['1']['proID'],
			'2' => $list['2']['proID'],
			'3' => $list['3']['proID'],
			'4' => $list['4']['proID']
			);
		
		return $arrHot;
	}

	/**
	* 检查是否投票过
	* @param int $xmu_num
	* @param int $proID
	* @return bool
	*/
	public static function isVoted($xmu_num, $proID){
		$db = Vera_Database::getInstance();
		$conds = array(
			'xmu_num' => $xmu_num,
			'proID' => $proID
			);
		if(!$ret=$db->select('voteLog','*',$conds)){//没有投过票
			return false;
		}
		return $ret;
	}

	/**
	* 投票信息记录到voteLog
	* @param int $xmu_num
	* @param int $proID
	* @return int 
	*/
	public static function addVoteLog($xmu_num, $proID, $actID){
		$db = Vera_Database::getInstance();
		
		$rows = array(
			'proID' => $proID,
			'actID' => $actID,
			'xmu_num' => $xmu_num,
			'date' => date('H-m-d H:i:s')
			);
		if(!$ret = $db->insert('voteLog',$rows)){
			return false;
		}
		return $ret;
	}

	/**
	* votePro['poll']投票数量+1 
	* @param int $proID
	*/
	public static function addProPoll($proID){
		$db = Vera_Database::getInstance();
		$rows = array('poll' => $tem['poll']+1);
		$conds = array('proID' => $proID);
		if(!$ret = $db->update('votePro',$rows, $conds)){//pro['poll'] += 1
			return false;
		}
		self::_setHotPro($tem['actID'],$tem['proID']);
		return $ret;
	}

	/**
	* voteLog投票信息删除 需要检查act是否结束
	* @param array $userInfo
	* @param int $proID
	* @return int 投票数量
	*/
	public static function deleteVoteLog($xmu_num,$proID){
		$db = Vera_Database::getInstance();

		$conds = array(
			'proID' => $proID,
			'xmu_num' => $xmu_num
			);
		if(!$ret = $db->delete('voteLog', $conds)){//删除Log
			return fasle;
		}
		return $ret;
	}

	/**
	* votePro['poll']投票数量-1 
	* @param int $proID
	*/
	public static function subProPoll($proID){
		$db = Vera_Database::getInstance();
		$rows = array('poll' => $tem['poll']-1);
		$conds = array('proID' => $proID);
		if(!$ret = $db->update('votePro',$rows, $conds)){//pro['poll'] -= 1
			return false;
		}
		self::_setHotPro($tem['actID'],$tem['proID']);
		return $ret;
	}

	/**
	* 设置某投票活动票数前5的项目
	* @param actID
	* @return array
	*/
	public static function _setHotPro($actID,$proID){
		$cache = Vera_Cache::getInstance();
		$db = Vera_Database::getInstance();
		$key_hot = 'vote_'. $actID. '_hotProID';

		$arr_hot = $cache->get($key_hot);
		if(!$arrhot){//缓存中没有则重新获取前五
			$arrhot = array();
			$append = 'order by poll desc';
			$list = $db->select('votePro', '*', NULL, $append);
			$arrhot = array(
				'0' => $list['0']['proID'],
				'1' => $list['1']['ProID'],
				'2' => $list['2']['proID'],
				'3' => $list['3']['proID'],
				'4' => $list['4']['proID']
				);
		}else{//缓存里有前五的proID
			$conds = array('proID' => $proID);
			$proInfo = $db->select('votePro', '*', $conds);	
			for($i = 4; $i > -1; $i--){
				if($arrHot["$i"] == $proID){////$proID 商品已经在前五
					for($j = $i-1; $j > -1; $j--, $i--){

						$key = 'vote_' . $actID .'_' . $arrHot["$j"] . '_info';
						$hot = $cache->get($key);
						if(!$hot){//缓存中没有$arrHot["$j"]的信息
							$conds = array('proID' => $proID);
							$hot = $db->select('votePro', '*', $conds);	
						}

						if($hot['poll'] > $proInfo['poll']){
							return ;
						}
						$tem = $hot['proID'];
						$arrHot["$j"] = $proInfo['proID'];
						$arrHot["$i"] = $tem;
					}
					return true;
				}
				else if($i == -1){//$proID 不再前五中
					for($i = 4; $i > -1; $i--){
						$conds = array('proID' => $arrHot["$i"]);
						$hot = $db->select('votePro', '*', $conds);
						if($last['poll'] > $proInfo['poll']){
							return false;
						}
						$tem = $hot['proID'];
						$arrHot["$j"] = $proInfo['proID'];
						$arrHot["$i"] = $tem;
					}
				}
			}
		}
		$cache->set($key_hot, $arr_hot, time()+3600*24*30);
	}

	// /**
	// * 获取某人对某投票活动的剩余投票次数
	// * @param int $xmu_num
	// * @param int $actID
	// * @return int
	// */
	// public static function getVoteRemainMount($xmu_num,$actID){

	// }


	// /**
	// * 通过项目ID获取投票记录 按时间升序排列
	// * @param int $proID
	// * @return array
	// */
	// public static function getVoteLogByProID($proID){
	// 	$db = Vera_Database::getInstance();
	// 	$conds = array('proID' => $proID);
	// 	$append = 'order by date desc';
	// 	$ret = 

	// }

	// *
	// * 通过活动ID获取投票记录 按时间升序排列
	// * @param int $actID
	// * @return array
	
	// public static function getVoteLogByActID($actID){

	// }

	// /**
	// * 通过学号获取投票记录 按时间升序排列
	// * @param int $xmu_num
	// * @return array
	// */
	// public static function getVoteLogByXum_num($xmu_num){

	// }
	// /**
	// * 获取某人对某投票活动的剩余投票次数
	// * @param int $xmu_num
	// * @param int $actID
	// * @return int
	// */
	// public static function getVoteRemainMount($xmu_num,$actID){

	// }

	// /**
	// * 检查限制条件是否满足
	// * @param array $userInfo
	// * @param array $limits
	// */
	// public static function checkLimits($userInfo, $limits){

	// }

	

}
?>