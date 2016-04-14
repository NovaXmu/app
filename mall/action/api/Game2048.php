<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Game2048.php
*	description:	2048相关
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Api_Game2048 extends Action_Base
{
	public $resource;
	private static $arrBlackListLogBuffer = array();
	private $awardTimes;
	
	function __construct()
	{
		$this->resource = parent::getResource();
		$tmp = Vera_Cache::getInstance()->get('mall_awardTimes');
		$this->awardTimes = $tmp ? $tmp : 1;
	}

	function run() 
	{
		if (isset($_REQUEST['m']))
		{
			switch ($_REQUEST['m']) {
				case 'start':
    				return $this->startGame();
    				break;
    			case 'saveScore':
    				return $this->saveScore();
    				break;
    			case 'rank':
    				return $this->rank();
    				break;
    		}
    	}
    	$ret = array('errno' => '1','errmsg' => '参数不对');
    	echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
	}

	function startGame()
	{
		$lastPlayID = Data_Db::getLastPlayID($this->resource['yb_userid']);
		$key = "NovaStudio_" . $this->resource['yb_userid'];
		$code = Library_Encrypt::encrypt($key , $lastPlayID);
		Vera_Log::addNotice('IP', $_SERVER["REMOTE_ADDR"]);
		Vera_Log::addNotice('stuNum', $this->resource['yb_studentid']);
		Vera_Log::addNotice('code', $code);
		$data = array(
			'code' => $code,
			'highestScore' => Data_Db::getTodayHighestScore($this->resource['yb_userid'])
			);
		echo json_encode(array("errno" => 0, "errmsg" => 'ok', "data" => $data));
		return true;
	}

	function saveScore(){
		if (!isset($_POST['score']) || !is_numeric($_POST['score']) || !isset($_POST['code']))
		{
			echo json_encode(array('errno' => '1','errmsg' => '参数不对'), JSON_UNESCAPED_UNICODE);
			return false;
		}
		$isCodeLegal = $this->checkCodeLegal($_POST['code']);
		$isUserLegal = Data_Cache::checkUserLegal($this->resource['yb_userid']);
		if (!$isCodeLegal || !$isUserLegal)
		{
			$arrLog = array(
				'isCodeLegal' => $isCodeLegal ? '合法' : '非法',
				'isUserLegal' => $isUserLegal ? '合法' : '非法'
				);
			self::$arrBlackListLogBuffer += $arrLog;
			$this->addBlackListLog();
			Data_Cache::addUserBlackList($this->resource['yb_userid']);
			Data_Cache::addIPBlackList($_SERVER["REMOTE_ADDR"]);
			echo json_encode(array('errno' => 1,'errmsg' => '非法请求'), JSON_UNESCAPED_UNICODE);
			return false;
		}
		$currentScore = $_POST['score'];
		$highestScore = Data_Db::getTodayHighestScore($this->resource['yb_userid']);
		$ret = array('errno' => 0,'errmsg' => '未打破今日记录', 'award' => 0);
		if ($highestScore < $currentScore)
		{
			$ret = $this->award($currentScore, $highestScore);
		}
		Data_Db::saveScore($this->resource['yb_userid'], $currentScore, isset($ret['award']) ? $ret['award'] : 0, $this->awardTimes);
		$ret['award'] = "{$ret['award']} * {$this->awardTimes} = " . $ret['award'] * $this->awardTimes;
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	} 
	
	function rank(){
		$res = array('errno'=>0,'errmsg'=>'OK','data'=>array());
		$data = Data_Db::getScoreRank(10, 'game2048');
		$cache = Vera_Cache::getInstance();
		if ($data)
		{

			foreach ($data as $each) {
				$key = 'yb_user_info_' . $each['yb_uid'];
				$tem = $cache->get($key);
				$each['yb_usernick'] = isset($tem['yb_usernick']) ? $tem['yb_usernick'] : $each['yb_uid'];
				$res['data'][] = $each;
			}
		}
		if (!$res['data'])
			$res = array('errno'=>1,'errmsg'=>'今天还没有人获得网薪','data'=>array());
		echo json_encode($res, JSON_UNESCAPED_UNICODE);
		return true;
	}

	function addBlackListLog()
	{
		$add = array(
			'time' => date("Y-m-d H:i:s"),
			'IP' => $_SERVER["REMOTE_ADDR"],
			'code' => $_POST['code'],
			'name' => $this->resource['yb_realname'],
			'stuNum' => $this->resource['yb_studentid'],
			'yb_userid' => $this->resource['yb_userid']
			);
		$array = array_merge($add, self::$arrBlackListLogBuffer);
		foreach ($array as $key => $value) {
			$array[$key] = "[{$key}]$value";
		}
		$str = str_repeat("---", 25);
		array_push($array, $str);
		Vera_Log::addLog('blackList', $array);
	}
	
	function checkCodeLegal($code)
	{
		if (!is_string($code) || (strlen($code) % 4))//base64编码的字符串长度是4的整数倍
		{
			$arrLog = array('errmsg' => 'code不是字符串或长度不合法');
			self::$arrBlackListLogBuffer += $arrLog;
			return false;
		}
		$key = "NovaStudio_" . $this->resource['yb_userid'];
		$str = Library_Encrypt::decrypt($key, $code);
		$arr = json_decode($str, true);
		$lastPlayID = Data_Db::getLastPlayID($this->resource['yb_userid']);
		$timeInterval = time() - strtotime($arr['time']);
		$variable = $arr['variable'];
		if ($timeInterval < 1 || $variable != $lastPlayID)
		{
			$arrLog = array(
				'errmsg' => '解密得到的数据不合法',
				'decryptData' => json_encode($arr), 
				'timeInterval' => $timeInterval, 
				'lastPlayID' => $lastPlayID);
			self::$arrBlackListLogBuffer += $arrLog;
			return false;
		}
		return true;
	}

	function award($newScore, $oldScore)
	{
		$oldAward = Library_Game::getAwardByScore($oldScore);
		$newAward = Library_Game::getAwardByScore($newScore);
		$addAward = $newAward - $oldAward;
		if ($addAward > 0)
		{
			Vera_Autoload::changeApp('yiban');
			$res = Data_Yiban::awardSalary($this->resource['yb_userid'], $this->resource['access_token'], $addAward * $this->awardTimes);
			if (!$res) {
				return array('errno' => 1, 'errmsg' => "啊哦，{$addAward}网薪发放失败，大概贵校还没跟我们合作哟", 'award' => 0);
			}
			Vera_Autoload::reverseApp();
			Action_Api_Helper::refreshUserInfo($this->resource);
			return array('errno' => 0, 'errmsg' => '网薪发放成功', 'award' => $addAward);
		}
		else
		{
			return array('errno' => 1, 'errmsg' => '虽打破纪录，未获得网薪', 'award' => $addAward);//当发放0网薪时会失败
		}
	}
}
?>