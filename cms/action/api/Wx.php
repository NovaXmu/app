<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Wx.php
*	description:	网薪相关接口，发网薪以及网薪数据统计
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Api_Wx
{
	public function run()
	{
		//session_start();
		if (!isset($_GET['m']))
		{
			return;
		}

		$m = $_GET['m'];
		switch ($m) {
			case 'distribute':
				return $this->distribute();
				break;
			case 'deduct':
				return $this->deduct();
			case 'batchDistribute':
				return $this->batchDistribute();
			default:
				break;
		}
		return array('errno' => 1, 'errmsg' => '非法请求');
	}

	public function distribute()
	{
		if (!isset($_POST['yibanIds']) || !isset($_POST['awards'])){
			echo json_encode(array('errno' => 1, 'errmsg' => '参数不对' ), JSON_UNESCAPED_UNICODE);
			return false;
		}
		$yibanIds = json_decode($_POST['yibanIds'], true);
		$awards = json_decode($_POST['awards'], true);
		if (count($yibanIds) != count($awards)){
			echo json_encode(array('errno' => 1, 'errmsg' => '参数不对' ), JSON_UNESCAPED_UNICODE);
			return false;
		}
		$descriptions = isset($_POST['descriptions']) ? json_decode($_POST['descriptions'], true) : array();
		$db = new Data_Wx();
		$flag = 0;
		Vera_Autoload::changeApp('yiban');
		foreach ($yibanIds as $index => $yibanId) {
			$accessToken = $db->getAccessToken($yibanId);
			if (!$accessToken){
				$flag = 1;
				$res[$index] = array('errno' => 1, 'errmsg' => '无有效易班token');
				$db->insertWxLog($_SESSION['id'], $yibanId, $awards[$index], isset($descriptions[$index]) ? $descriptions[$index] : '', $res[$index]['errmsg']);
				continue;
			}
			if (Data_Yiban::awardSalary($yibanId, $accessToken, $awards[$index])){
				$res[$index] = array('errno' => 0, 'errmsg' => 'ok');
			}else {
				$flag = 1;
				$res[$index] = array('errno' => 1, 'errmsg' => '未知错误');
			}
			$db->insertWxLog($_SESSION['id'], $yibanId, $awards[$index], isset($descriptions[$index]) ? $descriptions[$index] : '', $res[$index]['errmsg']);
		}
		echo json_encode(array('errno' => $flag, 'errmsg' => $flag ? '操作未全部成功' : 'ok','data' => $res), JSON_UNESCAPED_UNICODE);
		return true;
	}

	public function batchDistribute()
	{
		if (!isset($_POST['yibanIds']) || !isset($_POST['award']) || !isset($_POST['description'])){
			echo json_encode(array('errno' => 1, 'errmsg' => '参数不对' ), JSON_UNESCAPED_UNICODE);
			return false;
		}
		$yibanIds = explode(' ', $_POST['yibanIds']);
		$award = $_POST['award'];
		$description = $_POST['description'];
		$db = new Data_Wx();
		$flag = 0; 
		Vera_Autoload::changeApp('yiban');
		foreach ($yibanIds as $index => $yibanId) {
			$accessToken = $db->getAccessToken($yibanId);
			if (!$accessToken){
				$flag = 1;
				$res[$index] = array('errno' => 1, 'errmsg' => '无有效易班token');
				$db->insertWxLog($_SESSION['id'], $yibanId, $award, $description, $res[$index]['errmsg']);
				continue;
			}
			if (Data_Yiban::awardSalary($yibanId, $accessToken, $award)){
				$res[$index] = array('errno' => 0, 'errmsg' => 'ok');
			}else {
				$flag = 1;
				$res[$index] = array('errno' => 1, 'errmsg' => '未知错误');
			}
			$db->insertWxLog($_SESSION['id'], $yibanId, $award, $description, $res[$index]['errmsg']);
		}
		echo json_encode(array('errno' => $flag, 'errmsg' => $flag ? '操作未全部成功' : 'ok','data' => $res), JSON_UNESCAPED_UNICODE);
		return true;
	}

	public function deduct()
	{
		if (!isset($_POST['yibanIds']) || !isset($_POST['awards'])){
			echo json_encode(array('errno' => 1, 'errmsg' => '参数不对' ), JSON_UNESCAPED_UNICODE);
			return false;
		}
		$yibanIds = json_decode($_POST['yibanIds'], true);
		$awards = json_decode($_POST['awards'], true);
		if (count($yibanIds) != count($awards)){
			echo json_encode(array('errno' => 1, 'errmsg' => '参数不对' ), JSON_UNESCAPED_UNICODE);
			return false;
		}
		$descriptions = isset($_POST['descriptions']) ? json_decode($_POST['descriptions'], true) : array();
		$db = new Data_Wx();
		$flag = 0;
		Vera_Autoload::changeApp('yiban');
		foreach ($yibanIds as $index => $yibanId) {
			$accessToken = $db->getAccessToken($yibanId);
			if (!$accessToken){
				$flag = 1;
				$res[$index] = array('errno' => 1, 'errmsg' => '无有效易班token');
				continue;
			}
			if (Data_Yiban::paySalary($yibanId, $accessToken, $awards[$index])){
				$res[$index] = array('errno' => 0, 'errmsg' => 'ok');
			}else {
				$flag = 1;
				$res[$index] = array('errno' => 1, 'errmsg' => '未知错误');
			}
			$db->insertWxLog($_SESSION['id'], $yibanId, -$awards[$index], isset($descriptions[$index]) ? $descriptions[$index] : '', $res[$index]['errmsg']);
		}
		echo json_encode(array('errno' => $flag, 'errmsg' => $flag ? '操作未全部成功' : 'ok','data' => $res) , JSON_UNESCAPED_UNICODE);
		return true;
	}
}
?>