<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Jssdk.php
*	description:	JS-SDK一些参数获取
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 	s
*/
class Action_Api_Public_Jssdk
{
	public $cache;
	function __construct()
	{
		$this->cache = Vera_Cache::getInstance();
	}

	function run()
	{
		$wechat = new Data_Wechat();

		$access_token = $wechat->getAccessToken();
		$jsapi_ticket = $wechat->getJsapiTicket();
		$appId = $wechat->appId;
	
		$noncestr = "novaxmu";
		$timestamp = time();

		$url = "http://{$_SERVER['SERVER_NAME']}/templates/cargo/dist/index.html";
		//此处的url必须在微信后台配置jssdk时对应的域名下，所以用$_SERVER['SERVER_NAME']获取当前域名，与微信网页授权snsapi_base、snsapi_userinfo类同
		
		$str = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
		$signature = sha1($str);
		echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => array('noncestr' => $noncestr, 'timestamp' => $timestamp, 'signature' => $signature, 'appId' => $appId)));
	}

	
}