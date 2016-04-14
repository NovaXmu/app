<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Auth.php
*	description:	cargo
*
*	@author Nili
*	@license Apache v2 License
*	
**/
/**
* 
*/
class Action_Auth
{
	static $passList = array(
		'Api_Public_Linkin',
		'Admin',
		'Api_Admin'
		);

	static $publicAction = array(
		'Category',
		'Item',
		'User',
		'Api_Public_Take',
		'Api_Jssdk',
		'Api_Public_Buy'
		);

	public static function run () {
//		session_start();
		if (empty($_SESSION['openid'])) {
			//跳转至微信授权
			$conf = Vera_Conf::getConf('global');
			$conf = $conf['wechat'];
			$appID = $conf['AppID'];
			$redirectUrl = "http://{$_SERVER['SERVER_NAME']}/yiban/entry";
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=$redirectUrl&response_type=code&scope=snsapi_base&state=cargo#wechat_redirect*/
";
			header("location: $url");
			exit();
		}

		if (strpos(ACTION_NAME, "Api_Public") !== false) {
			return true;
		}

		$data = new Data_Db();

		if (!isset($_SESSION['user_id'])) {
			$user = $data->getUser(array('openid' => $_SESSION['openid'], 'deleted' => 0));
			if (!empty($user)) {
				$admin = $data->getPrivilege(array('user_id' => $_SESSION['user_id'], 'deleted' => 0));
				if (!empty($admin)) {
					$_SESSION['isAdmin'] = 1;
				}
				$_SESSION['user_id'] = $user[0]['id'];
			}
		}

		if (strpos(ACTION_NAME, 'Api_Admin') !== false) {
			if (!isset($_SESSION['isAdmin'])) {
				echo json_encode(array('errno' => 1, 'errmsg' => '非管理员，无权进行该操作'), JSON_UNESCAPED_UNICODE);
				return false;
			}
		}
		return true;
	}
}