<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			XmuMail.php
*	description:	厦大邮箱每日激活验证码可多获得5次博饼机会
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 厦大邮箱每日激活验证码可多获得5次博饼机会			
*/
class Action_Api_XmuMail	
{
	
	function run()
	{
		if (!isset($_GET['m']) || empty($_GET['m'])) {
			return;
		}
		switch ($_GET['m']) {
			case 'getCode':
				$this->sendCode();
				break;
			default:
				# code...
				break;
		}
	}

	function sendCode()
	{
		if ($_SESSION['yb_user_info']['yb_identity'] != '学生') {
			echo json_encode(array('errno' => 1, 'errmsg' => '该活动只面向学生开放'), JSON_UNESCAPED_UNICODE);
			return;
		}
		$cache = Vera_Cache::getInstance();
		if ($cache->get('mall_xmuMailBobingTimes_' . $_SESSION['yb_user_info']['yb_userid'])) {
			echo json_encode(array('errno' => 1, 'errmsg' => '今日已获取该奖励'), JSON_UNESCAPED_UNICODE);
			return;
		}
		$alphabet = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$code = '';
		for ($i=0; $i < 10; $i++) { 
			$code .= $alphabet[rand(0,35)];
		}
		$cache->set($code, $_SESSION['yb_user_info']['yb_userid'], strtotime('tomorrow'));
		
		$mailAddress = $_SESSION['yb_user_info']['yb_studentid'] . '@stu.xmu.edu.cn';
		$subject = '网薪换实物';
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$url = "http://" . $_SERVER['HTTP_HOST'] . "/mall/api/public/XmuMail?m=verifyCode&code=$code&yb_userid={$_SESSION['yb_user_info']['yb_userid']}";
		$message = "
			<html>
			<body>
				<p>网薪换实物额外次数领取(当日内有效，点击后失效）</p>
				<p>$url</p>
			</body>
			</html>
		";
		if (mail($mailAddress, $subject, $message, $headers)) {
			echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $_SESSION['yb_user_info']['yb_studentid'] . '@stu.xmu.edu.cn'), JSON_UNESCAPED_UNICODE);
			return ;
		}
		echo json_encode(array('errno' => 1, 'errmsg' => '邮件发送失败'), JSON_UNESCAPED_UNICODE);
	}
}