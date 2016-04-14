<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Auth.php
*	description:	权限验证
*
*	@author Nili
*	@license Apache v2 License
*	
**/
class Action_Auth extends Action_Base
{
	function __construct() {}

	public static function run()
	{
//		session_start();
		//Vera_Log::addLog('auth', 'time: ' . date('Y-m-d H:i:s') . ' session: ' . json_encode($_SESSION, JSON_UNESCAPED_UNICODE));
		if (strpos(ACTION_NAME, 'Api_Public') !== false) {//public免验证
			return true;
		}
		if (empty($_SESSION['yb_user_info']) || $_SESSION['yb_user_info']['token_expires'] <= date("Y-m-d H:i:s")) {
			if (isset($_GET['verify_request'])) {
				header("location:/yiban/EntryFromYiban?appName=mall&verify_request={$_GET['verify_request']}");
				exit();
			}
			header("location:/yiban/EntryFromYiban?appName=mall");
			exit();
		}

		parent::setResource($_SESSION['yb_user_info']);
		if ($_SESSION['yb_user_info']['yb_schoolname'] == '厦门大学') {
			return true;
		}
		if (in_array(ACTION_NAME, array("Api_Auction", "Api_Exchange"))) {
			$ret = array('errno' => 1, 'errmsg' => "不能购买非本校的商品");
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}
		return true;
	}
}

?>