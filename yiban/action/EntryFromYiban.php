<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			EntryFromYiban.php
*	description:	易班应用入口.
*					
*
*	@author Nili
*	@license Apache v2 License
*	
**/		


/**
* 根据appName参数分辨不同app。
*此入口完成授权并获取实名信息，再根据appName路由至不同app。
*在首次授权或授权过期时调用。
*统一通过nova来授权，并获取实名信息。
*所有易班应用都可以不再申请新的应用，仅仅通过nova获取实名信息即可
*
* update 	2015-11-19 新增verify_request方式获取信息（仅易班客户端进入会使用到）
*/
 
class Action_EntryFromYiban 
{
	public function run () {
//		session_start();

		echo 'run in entryFromYiban' .PHP_EOL;

		if (!isset($_SESSION['appName']) && (!isset($_GET['appName']) || empty($_GET['appName'])))
		{
			return false;
		}
		if (isset($_GET['appName']) && !empty($_GET['appName']))
		{
			$_SESSION['appName'] = $_GET['appName'];
		}

		//授权第一步，获取access_token,通过verify_request解密（仅易班客户端进入时）或者code参数换取
		$yibanConf = Vera_Conf::getConf('yiban');
		$conf = $yibanConf['nova'];
		$cache = Vera_Cache::getInstance();
		$yb = Library_YbOpenApi::getInstance();
		$yb = $yb->init($conf['AppID'],$conf['AppSecret'],$conf['CALLBACK']);
		$au = $yb->getAuthorize();

		if (isset($_GET['verify_request']) && !empty($_GET['verify_request'])) {
			//来自易班官方文档
			$postObject = addslashes($_GET["verify_request"]);
			$postStr = pack("H*", $postObject);
			$conf = $yibanConf[$_SESSION['appName']];//解密只能用该应用对应的信息
			$appID = $conf['AppID'];//应用appID
			$appSecret = $conf['AppSecret'];//应用appSecret
			if (strlen($appID) == 16) {
				$postInfo = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $appSecret, $postStr, MCRYPT_MODE_CBC, $appID);
			} else {
				$postInfo = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $appSecret, $postStr, MCRYPT_MODE_CBC, $appID);
			}
			$postInfo = rtrim($postInfo);
			if (!empty($postInfo["visit_oauth"]["access_token"]) && !empty($postInfo["visit_oauth"]["token_expires"])) {
				$info = $postInfo["visit_oauth"];
			} else {
				if (!$this->checkRepeatRequest('verify_request')) {
					return false;
				}
			}
		} else {
			if (isset($_GET['code']) && !empty($_GET['code']))
			{
				if (!$this->checkRepeatRequest('code')) {
					return false;
				}
				$info = $au->queryToken($_GET['code']);
				//info是数组，正确情况key分别access_token、userid、expires，错误时，key为code、msgCN、msgEN
			}
		}

		//第二步，通过access_token获取实名信息
		if (isset($info['access_token']))
		{
			$yb = $yb->bind($info['access_token']);
			$user = $yb->getUser();
			$userInfo = $user->realme();

			if ($userInfo['status'] == 'success')
			{

				$info['token_expires'] = date('Y-m-d H:i:s', $info['expires']);
				Vera_Log::addLog('auth', 'time: ' . date('Y-m-d H:i:s') . ' info:' . json_encode($userInfo['info'], JSON_UNESCAPED_UNICODE));
				Service_Func::saveRealInfo($userInfo['info'],$info);
				header("Location:/{$_SESSION['appName']}");
				return true;
			}else{
				return false;
			}
		}
		else {
			$tmp = array('msg' => '非法code或verify_request，无法获取access_token', 'count' => 1);
			$cacheKey = isset($_GET['code']) ? $_GET['code'] : $_GET['verify_request'];
			$cacheKey = substr($cacheKey, 0, 10);//verify_request太长了好像有点问题
			$cacheMsg = $cache->get('yiban_' . $cacheKey);
			if ($cacheMsg && $tmp = json_decode($cacheMsg, true)){
				$tmp['count'] ++;
			}
			$cache->set('yiban_' . $cacheKey, json_encode(array_merge($_GET, $tmp), JSON_UNESCAPED_UNICODE), time() + 300);
		}
		header('location: ' . $au->forwardurl());
		exit();
	}

	public function checkRepeatRequest ($key) {
		$cache = Vera_Cache::getInstance();
		$cacheKey = substr($_GET[$key], 0, 10);//verify_request太长了好像有点问题
		$cacheMsg = $cache->get('yiban_' . $cacheKey);
		if ($cacheMsg && $judge = json_decode($cacheMsg, true)){
			Vera_Log::addLog('tmp', json_encode(array(
				'IP' => $_SERVER["REMOTE_ADDR"],
				'time' => date('Y-m-d H:i:s'),
				'info' => $cacheMsg,
				'key' => $_GET[$key],
				'appName' => $_SESSION['appName']), JSON_UNESCAPED_UNICODE));
			// $judge = json_decode($cacheMsg, true);
			if ($judge['count'] > 5){//重复请求大概在3-4次
				echo "非法令牌，请更新至最新版易班客户端重新进入。";
				return false;
			}
		}
		return true;
	}
}
?>