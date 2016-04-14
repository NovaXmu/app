<?php
/**
* @copyright
*
* file: Vote.php
* description: 投票平台Service层，投票操作相关
*
* @author linjun
*/
class Service_Vote{
	private static $resource = NULL;

	function __construct($resource = NULL){
		self::$resource = $resource;
	}


	/**
	* 投票
	* @return array 投票结果
	*/
	public function vote(){

		$actID = self::$resource['actID'];
		$proID = self::$resource['proID'];
		$xmu_num = self::$resource['xmu_num'];

		if(!self::_check($actID, $proID)){
			return false;
		}

		$data = new Data_Db(self::$resource);
		
		//投票
		$ret = $data->addVoteLog($xmu_num, $proID, $actID);//记录到voteLog
		if(!$ret){
			throw new Exception("添加投票记录到voteLog失败", 4107);
		}
		$ret = $data->addProPoll($proID);
		if(!$ret){
			throw new Exception("投票更新到votePro失败", 4108);
		}
		return '投票成功！';
	}

	public function unvote(){

		$actID = self::$resource['actID'];
		$proID = self::$resource['proID'];
		$xmu_num = self::$resource['xmu_num'];

		if(!self::_check($actID, $proID)){
			return false;
		}

		$data = new Data_Db(self::$resource);

		//取消投票
		$ret = $data->deleteVoteLog($xmu_num, $proID);//记录到voteLog
		if(!$ret){
			throw new Exception("删除投票记录到voteLog失败", 4107);
		}
		$ret = $data->subProPoll($proID);
		if(!$ret){
			throw new Exception("投票更新到votePro失败", 4108);
		}
		return '取消投票成功！';
	}

	/**
	* 检查活动及项目是否合法
	* @param int $actID
	* @param int $proID
	* @return bool
	*/
	public static function _check($actID, $proID){
		$data = new Data_Db(self::$resource);

		//检查是否绑定
		if(!$data->checkLink()){
			throw new Exception('没有绑定厦大账号',4101);
			return false;
		}

		//检查活动合法
		$actInfo = $data->getActInfo($actID);
		if(!$actInfo){
			throw new Exception('活动不存在',4102);
			return false;
		}
		if(strtotime($actInfo['startTime']) > time()){
			 throw new Exception("尚未开始投票", 4103);
			 return false;
		}
		if(strtotime($actInfo['endTime']) > time()){
			 throw new Exception("已经结束投票", 4104);
			 return false;
		}

		//检查项目是否合法
		$proInfo = $data->getProInfo($proID);
		if(!$proInfo){
			throw new Exception("项目不存在", 4105);
			return false;
		}

		//检查是否投过票
		$xum_num = self::$resource['xmu_num'];
		$log = $data->isVoted($xmu_num, $proID);
		if($log){//投过票了
			throw new Exception("已经投过票", 4106);
			return false;
		}

		return true;
	}
}
?>