<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Linkin.php
*	description:	绑定身份
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 身份绑定
*/
class Action_Api_Public_Linkin
{
	
	function run()
	{
		if (!isset($_SESSION['openid']) || empty($_SESSION['openid']) || !isset($_POST['mobile_phone']) || empty($_POST['mobile_phone'])) {
			Vera_Log::addVisitLog('res', '参数有误');	
			echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
			return;
		}

		$data = new Data_Db();
		$user = $data->getUser(array('mobile_phone' => $_POST['mobile_phone'], 'deleted' => 0));
		if (empty($user)) {
			Vera_Log::addVisitLog('res', '用户信息不存在，无法绑定');	
			echo json_encode(array('errno' => 1, 'errmsg' => '用户信息不存在，无法绑定'), JSON_UNESCAPED_UNICODE);
			return;
		}
		if (!empty($user[0]['openid'])) {
			Vera_Log::addVisitLog('res', '该账号已被绑定');
			echo json_encode(array('errno' => 1, 'errmsg' => '该账号已被绑定'), JSON_UNESCAPED_UNICODE);
			return;
		}

		$data->setUser(array('openid' => $_SESSION['openid']), $user[0]['id']);
		Vera_Log::addVisitLog('res', 'ok');	
		$user[0]['openid'] = $_SESSION['openid'];
		$_SESSION['user_id'] = $user[0]['id'];
		echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $user[0]), JSON_UNESCAPED_UNICODE);
		return;
	}
}