<?php

/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Entry.php
*   description:      Action for Entry.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/

class Action_Entry extends Action_Base{

	function __construct($resource){
		parent::__construct($resource);
	}

	private static $xmu;
	private static $nova;
	
	public static function run(){

		if($_SERVER['HTTP_HOST'] == 'test.novaxmu.cn'){
			//linjiong
			$xmu = 'http%3a%2f%2ftest.novaxmu.cn%2fyiban%2fentry';
			//slim
			$nova = 'http%3a%2f%2ftest.novaxmu.cn%2fyiban%2fentry';
		}else{
			//linjiong
			$xmu = 'http%3a%2f%2fxmu.novaxmu.cn%2fyiban%2fentry';
			//slim
			$nova = 'http%3a%2f%2fwww.novaxmu.cn%2fyiban%2fentry';
		}

		if(!isset($_GET['code'])){
			return false;
		}
		switch($_GET['state']){
			//yiban
			case 'mall':
				return self::_entry($_GET['state'], $_GET['code']);
			case 'meet':
				return self::_entry($_GET['state'], $_GET['code']);
			case 'wap/together':
				return self::_entry($_GET['state'], $_GET['code']);
			//wechat
			case 'wap/sport':
				return self::_sport();
			case 'cargo':
				return self::_cargo();
			case 'ticket':
				self::_ticket();
				break;
			case 'roster/Checkin':
			case 'roster/Wechat':
				return self::_roster();
			case 'anniversary':
				return self::_anniversary();
			case 'checkin':
				return self::_checkin();
			case 'test':
				return self::_test();
			default:
				break;
		}
	}

	public static function _cargo()
	{
		$openid = self::_getWechatOpenid($_GET['code']);
		if (empty($openid)) {
			echo '获取openid失败';
			return;
		}
//		session_start();
		$_SESSION['openid'] = $openid;
		header("location: /cargo");
		exit();
	}

	public static function _ticket()
	{
		self::_getUserInfo(self::$nova, $_GET['state'], $_GET['code']);
		header('location:/'.$_GET['state']);
		exit;
	}

	public static function _roster(){
		self::_getUserInfo(self::$xmu, $_GET['state'], $_GET['code']);
		header('location:/'.$_GET['state']);
		exit();
	}

	public static function _anniversary(){
		self::_getUserInfo(self::$nova, $_GET['state'], $_GET['code']);
		header('location:/'.$_GET['state']);
		exit;
	}

	public static function _checkin(){
		self::_getUserInfo(self::$nova, $_GET['state'], $_GET['code']);
		header('location:/'.$_GET['state']);
		exit;
	}

	public static function _getWechatOpenid($code) 
	{
		$result = Vera_Conf::getConf('global');
		$appID = $result['wechat']['AppID'];
		$appSecret = $result['wechat']['AppSecret'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appID&secret=$appSecret&code=$code&grant_type=authorization_code";

		$curl = curl_init();
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1
			);
		curl_setopt_array($curl, $options);
		$json = curl_exec($curl);

		$data = json_decode($json, true);
		return isset($data['openid']) ? $data['openid'] : '';
	}


/*https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe2d52a227cf2eb97&redirect_uri=http://120.24.83.112&response_type=code&scope=snsapi_base&state=mall#wechat_redirect*/
/*https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe2d52a227cf2eb97&redirect_uri=http%3a%2f%2fwww.novaxmu.cn%2fyiban%2fentry&response_type=code&scope=snsapi_base&state=mall#wechat_redirect*/
	/**
	 * 		
	 * @param  [type] $code [description]
	 * @return [type]       [description]
	 * @author linjun 
	 * @rewrite by nili ,新加获取有效access_token之后获取实名信息并存入session
	 */
	public static function _entry($state, $code){
		$result = Vera_Conf::getConf('global');
		$appID = $result['wechat']['AppID'];
		$appSecret = $result['wechat']['AppSecret'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appID&secret=$appSecret&code=$code&grant_type=authorization_code";

		$curl = curl_init();
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1
			);
		curl_setopt_array($curl, $options);
		$json = curl_exec($curl);

		$data = json_decode($json, true);

		$db = Vera_Database::getInstance();

		$conditions = array('wechatOpenid' => $data['openid']);
		$data = $db->select('User','*',$conditions);
		if(empty($data) || !$data[0]['isLinkedYiban'])
		{
			header("location:http://www.novaxmu.cn/".$state);
			exit();
		}

		$conditions = array('uid'=>$data[0]['yibanUid']);
		$token = $db->select('Yiban', '*', $conditions);
		
		if(!$token || !$token[0]['accessToken'])//字段名啊啊啊啊啊啊啊啊啊
		{
			header("location:http://www.novaxmu.cn/".$state);
			exit();
		}

		if(date("Y-m-d H:i:s")>$token[0]['expireTime'])
		{
			header("location:http://www.novaxmu.cn/".$state);
			exit();
		}

//		session_start();
		$_SESSION['access_token'] = $token[0]['accessToken'];
		$_SESSION['token_expires'] = $token[0]['expireTime']; 
		$realInfo = Service_Func::getRealInfo($token[0]['accessToken']);
		if (!empty($realInfo))
		{
			//此处采用access_token来存session，目的是为了与易班接口保持一致
			Service_Func::saveRealInfo($realInfo, array('access_token' => $token[0]['accessToken'], 'token_expires' => $token[0]['expireTime']));
		}
		
		header("location:http://www.novaxmu.cn/".$state);

		return;
	}

	/**
	 * 		
	 * @author linjun 
	 */
	public static function _sport(){
		if (isset($_SESSION['userInfo']) && !empty($_SESSION['userInfo'])){
			Vera_Log::addLog('tmp', date('Y-m-d H:i:s') . ' ' . $_SERVER['REMOTE_ADDR'] . json_encode($_SESSION, JSON_UNESCAPED_UNICODE));
			header("location:/" . $_GET['state']);
			exit;
		}
		self::_getUserInfo(self::$nova, $_GET['state'], $_GET['code'], true);
		header('location:/'.$_GET['state']);
		exit;
	}

	private static function _getUserInfo($redirect_uri, $state, $code, $userInfo = false){
		$result = Vera_Conf::getConf('global');
		$appID = $result['wechat']['AppID'];
		$appSecret = $result['wechat']['AppSecret'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appID&secret=$appSecret&code=$code&grant_type=authorization_code";

		$curl = curl_init();
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1
			);
		curl_setopt_array($curl, $options);
		$json = curl_exec($curl);

		$data = json_decode($json, true);
		if (isset($data['errcode'])){
			Vera_Log::addLog('tmp', date('Y-m-d H:i:s') . ' ' . $_SERVER['REMOTE_ADDR'] . 'code有误，无法获取access_token' . json_encode($data,JSON_UNESCAPED_UNICODE));
			header("location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect");
			exit;
		}

//		session_start();
		$_SESSION['openid'] = $data['openid'];

		if($userInfo){
			//获取用户信息
			$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'].'&lang=zh_CN';
			$curl = curl_init();
			$options = array(
				CURLOPT_URL => $url,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1
				);
			curl_setopt_array($curl, $options);
			$json = curl_exec($curl);

			$data = json_decode($json, true);
			if (isset($data['errcode'])){
				Vera_Log::addLog('tmp', date('Y-m-d H:i:s') . ' ' . $_SERVER['REMOTE_ADDR'] . 'access_token有误，无法获取身份信息' . json_encode($data,JSON_UNESCAPED_UNICODE));
				header("location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appID&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect");
				exit;
			}
			$_SESSION['userInfo'] = array(
				'nickname' => $data['nickname'],
				'openid' => $data['openid'],
				'sex' => $data['sex'] == 1 ? '男' : '女',
				'city' => $data['city'],
				'country' => $data['country'],
				'province' => $data['province'],
				'headimgurl' => $data['headimgurl']
				);
			Data_Db::insertWechatUser($_SESSION['userInfo']);
		}
		Vera_Log::addLog('tmp', date('Y-m-d H:i:s') . ' ' . $_SERVER['REMOTE_ADDR'] . json_encode($_SESSION, JSON_UNESCAPED_UNICODE));
		return;
	}

}
?>