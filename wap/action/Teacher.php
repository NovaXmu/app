<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Teacher.php
*	description:	十佳老师投票，临时用
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 			
*/
class Action_Teacher
{
	public function run() 
	{
//		session_start();
		if (!isset($_SESSION['yb_user_info']) || empty($_SESSION['yb_user_info']))
		{
			header("Location:/yiban/entryFromYiban?appName=wap/teacher");
			exit;
		}
		$data = Data_Db::getAllTeachers();
		$logs = Data_Db::getVoteLog($_SESSION['yb_user_info']['yb_userid']);
		foreach ($data as $key => $value) {
			if (!empty($logs) && in_array($value['id'], array_column($logs,'teacher_id')))
			{
				$data[$key]['voted'] = 1;
				continue;
			}
			$data[$key]['voted'] = 0;
		}
		$voted = 0;
		if (!empty($logs))
		{
			$voted = 1;
		}

		$view = new Vera_View(true);
		$view->assign('data', $data);
		$view->assign('voted', $voted);
		$view->display('wap/Vote.tpl');
	}
	
}