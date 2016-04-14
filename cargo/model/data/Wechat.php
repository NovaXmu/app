<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Wechat.php
*	description:	微信接口相关
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 	x
*/
class Data_Wechat
{
	public $appId;
	public $appSecret;	
	public $cache;
	function __construct()
	{
		$conf = Vera_Conf::getConf('global');
		$this->appId = $conf['wechat']['AppID'];
		$this->appSecret = $conf['wechat']['AppSecret'];

		$this->cache = Vera_Cache::getInstance();
	}

	function getAccessToken() 
	{
		$access_token = $this->cache->get("wechat_accesstoken");
		if (!empty($access_token)) {
			return $access_token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => $url,
			CURLOPT_HEADER         => 0,
			CURLOPT_TIMEOUT  => 20,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			));
		$res = json_decode(curl_exec($ch), true);
		if (isset($res['access_token']) && !empty($res['access_token'])) {
			$this->cache->set('wechat_accesstoken', $res['access_token'], $res['expires_in'] - 60);
			return $res['access_token'];
		}
	}

	function getJsapiTicket() 
	{
		$jsapiTiket = $this->cache->get('wechat_jsapi_ticket');
		if (!empty($jsapiTiket)) {
			return $jsapiTiket;
		}
		
		$access_token = $this->getAccessToken();
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi",
			CURLOPT_HEADER         => 0,
			CURLOPT_TIMEOUT  => 20,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			));
		$res = json_decode(curl_exec($ch), true);
		if (isset($res['ticket']) && !empty($res['ticket'])) {
			$this->cache->set('wechat_jsapi_ticket', $res['ticket'], $res['expires_in'] - 60);
			return $res['ticket'];
		}
	}
}