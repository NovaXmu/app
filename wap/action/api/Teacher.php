<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Teacher.php
*	description:	十佳老师投票api，临时
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 	
*/
class Action_Api_Teacher
{
	
	public function run()
	{
//		session_start();
		if (!isset($_SESSION['yb_user_info']) || empty($_SESSION['yb_user_info']))
		{
			Vera_Log::addLog('teacher', array('separator' => str_repeat("---", 25),
				'time' => date("Y-m-d H:i:s"), 
				'ip' => 'IP:' . $_SERVER["REMOTE_ADDR"],
				'teacherId' => 'teacher_id: ' . $_GET['id']));
			header("Location:/yiban/entryFromYiban?appName=wap/teacher");
			exit;
		}
		$res = array('errno' => 1, 'errmsg' => '');
		if ($_SESSION['yb_user_info']['yb_schoolid'] != 314)
		{
			Vera_Log::addLog('teacher', array('separator' => str_repeat("---", 25),
				'time' => date("Y-m-d H:i:s"), 
				'ip' => 'IP:' . $_SERVER["REMOTE_ADDR"],
				'teacherId' => 'teacher_id: ' . $_GET['id'],
				'yb_user_info' => 'yb_user_info:' . json_encode($_SESSION['yb_user_info'], JSON_UNESCAPED_UNICODE)
				));
			$res['errmsg'] = '非本校用户不得投票';
			echo json_encode($res, JSON_UNESCAPED_UNICODE);
			return 0;
		}
		
		if (date("Y-m-d H:i:s") < '2015-08-14 12:00:00' || date("Y-m-d H:i:s") > '2015-09-11 24:00:00')
		{
			$res['errmsg'] = '不在投票时段内';
			echo json_encode($res, JSON_UNESCAPED_UNICODE);
			return 0;
		}

		$data = Data_Db::getVoteLog($_SESSION['yb_user_info']['yb_userid']);

		if (count($data) > 9)
		{
			$res['errmsg'] = '今日已投票';
			echo json_encode($res, JSON_UNESCAPED_UNICODE);
			return 0;
		}

		if (!isset($_GET['id']) || !is_array(json_decode($_GET['id'], true)))
		{
			$res['errmsg'] = '參數有误';
			echo json_encode($res, JSON_UNESCAPED_UNICODE);
			return 0;
		}
		$ids = json_decode($_GET['id'], true);
		$ids = array_flip($ids);//去除重复老师id
		if (count($ids) != 10)
		{
			$res['errmsg'] = '只能投10名老师，不可多不可少';
			echo json_encode($res, JSON_UNESCAPED_UNICODE);
			return 0;
		}

		foreach ($ids as $id => $index) {
			if (in_array($id, array_column($data, 'teacher_id')))
				{
				$res['errmsg'] = '同一老師只能投一票';
				echo json_encode($res, JSON_UNESCAPED_UNICODE);
				return 0;
			}
			Data_Db::vote($id, $_SESSION['yb_user_info']['yb_userid']);
		}
		Vera_Autoload::changeApp('yiban');
		Data_Yiban::awardSalary($_SESSION['yb_user_info']['yb_userid'], $_SESSION['yb_user_info']['access_token'], 300);
		Vera_Autoload::reverseApp();
		$res = array('errno' => 0, 'errmsg' => '投票成功，您已获得300网薪！');
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
	}
}