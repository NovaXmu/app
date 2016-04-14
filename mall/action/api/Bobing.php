<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Bobing.php
*	description:	博饼api
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Api_Bobing extends Action_Base
{
	private $awardTimes;

	function __construct()
	{
		$tmp = Vera_Cache::getInstance()->get('mall_awardTimes');
		$this->awardTimes = $tmp ? $tmp : 1;
	}

	public function run()
	{
		//判断参数
		if (isset($_GET['m'])) {
			switch ($_GET['m']) {
				case 'bobing':
					return $this->bobing();
					break;
				case 'rank':
					return $this->rank();
					break;
				case 'star':
					return $this->addYibanStarBobingTimes();
				case 'medal':
					return $this->addYibanMedalBobingTimes();
			}
		}
		$ret = array('errno' => '1', 'errmsg' => 'm参数不对');
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return false;
	}

	private function bobing()
	{
		$ret = array('errno' => '0', 'errmsg' => 'ok', 'data' => array());
		$resource = $this->getResource();
		$data = new Data_Db();
		$service = new Service_Earn();
		$times = $service->getRemainBobingTimes();
		if ($times < 1) {
			$ret = array('errno' => '1', 'errmsg' => '今日博饼次数已用完');
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}
		$res = Library_Game::bobing();//Bobing返回六个随机数及获得的网薪奖励
		if ($res) {
			$res['bobingLeval'] = Library_Game::getBobingLeval($res['money'], 1);
		}
		$res['remainTimes'] = $times - 1;
		Vera_Log::addNotice('yb_userid', $resource['yb_userid']);
		Vera_Autoload::changeApp('yiban');
		$ret['data'] = $res;
		if ($this->awardTimes > 1) {
			$ret['data']['money'] = "{$res['money']} * {$this->awardTimes} = " . $res['money'] * $this->awardTimes;
		}
		if (!Data_Yiban::awardSalary($resource['yb_userid'], $resource['access_token'], $res['money'] * $this->awardTimes)) {
			$ret['errno'] = 1;
			$tmp = $res['money'] * $this->awardTimes;
			$ret['errmsg'] = "啊哦，{$tmp}网薪发放失败，大概贵校还没跟我们合作哟";//ok？
			$res['money'] = 0;//发放失败，所得网薪数置零
		}
		Vera_Autoload::reverseApp();
		$data->addBobingLog($resource['yb_userid'], $res, $this->awardTimes);
		Action_Api_Helper::refreshUserInfo($resource);
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	//今日最佳的排行
	private function rank()
	{
		$res = array('errno' => 0, 'errmsg' => 'OK', 'data' => array());
		$tem = Data_Db::getEachBobingLog(10, 'bobing');
		$cache = Vera_Cache::getInstance();
		if ($tem) {
			foreach ($tem as $key => $each) {
				if ($each['award'])//0网薪不上榜
				{
					$each['bobingLeval'] = Library_Game::getBobingLeval($each['award']);
					$each['award'] *= $each['awardTimes'];
					$key = 'yb_user_info_' . $each['yb_uid'];
					$tem = $cache->get($key);
					if ($tem['yb_usernick']) {
						$each['yb_uid'] = $tem['yb_usernick'];
					}
					$res['data'][] = $each;
				}
			}
		}
		if (!$res['data'])
			$res = array('errno' => 1, 'errmsg' => '今天还没有人获得网薪', 'data' => array());
		echo json_encode($res, JSON_UNESCAPED_UNICODE);
		return true;
	}

	function addYibanStarBobingTimes()
	{
		//易班明星个人排行榜上的用户今日博饼次数领取
		$service = new Service_YibanRank();
		$starRank = $service->getStarRankData();

		$yb_userid = $_SESSION['yb_user_info']['yb_userid'];
		$cache = Vera_Cache::getInstance();
		if ($cache->get('mall_starBobingTimes_' . $yb_userid)) {
			echo json_encode(array('errno' => 1, 'errmsg' => '今日已领取该奖励'), JSON_UNESCAPED_UNICODE);
			return;
		}

		$starYibanIds = array_column($starRank, 'yb_userid');
		if (!in_array($yb_userid, $starYibanIds)) {
			echo json_encode(array('errno' => 1, 'errmsg' => '非榜上用户不得领取该奖励'), JSON_UNESCAPED_UNICODE);
			return;
		}

		$cache->set('mall_starBobingTimes_' . $yb_userid, 5, strtotime('tomorrow'));
		echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
	}

	function addYibanMedalBobingTimes()
	{
		//易班班级EGPA日上升榜今日博饼次数领取
		$service = new Service_YibanRank();
		$rankData = $service->getMedalRankData();

		if (!isset($_GET['groupId']) || !is_numeric($_GET['groupId'])) {
			echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
			return;
		}
		$groupId = $_GET['groupId'];
		$yb_userid = $_SESSION['yb_user_info']['yb_userid'];
		$cache = Vera_Cache::getInstance();
		if ($cache->get('mall_medalBobingTimes_' . $yb_userid . '_' . $groupId)) {
			echo json_encode(array('errno' => 1, 'errmsg' => '今日已领取该奖励'), JSON_UNESCAPED_UNICODE);
			return;
		}
		if (!in_array($groupId, array_column($rankData, 'group_id'))) {
			echo json_encode(array('errno' => 1, 'errmsg' => '该群组今日未上榜'), JSON_UNESCAPED_UNICODE);
			return;
		}
		Vera_Autoload::changeApp('yiban');
		$publicGroup = Data_Yiban::getPublicGroup($_SESSION['yb_user_info']['access_token']);
		$groupIds = array_column($publicGroup['public_group'], 'group_id');
		Vera_Autoload::reverseApp();

		if (!in_array($groupId, $groupIds)) {
			echo json_encode(array('errno' => 1, 'errmsg' => '当前用户不在该群组内'), JSON_UNESCAPED_UNICODE);
			return;
		}
		$cache->set('mall_medalBobingTimes_' . $yb_userid . '_' . $groupId, 5, strtotime("tomorrow"));

		//考虑到同一用户同时存在于多个公共群，并且这些群可能同时在榜上，所以增加totalGroupBobingTimes数据
		$totalGroupAwardTimes = $cache->get('mall_totalGroupBobingTimes' . $yb_userid);
		$cache->set('mall_totalGroupBobingTimes_' . $yb_userid, $totalGroupAwardTimes + 5, strtotime('tomorrow'));
		echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
	}
}
