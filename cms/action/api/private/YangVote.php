<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			yibanVote.php
*	description:	网薪换实物刷票
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 杨宇星的公众号刷票		
*/
class Action_Api_Private_YangVote
{
	
	function run()
	{
		date_default_timezone_set('Asia/Shanghai');
		set_time_limit(0);

		$path = SERVER_ROOT . '/app/cms/action/api/private/voice/data.csv';
		$dataFp = fopen($path, 'r');
		$start = time() - strtotime(date('Y-m-d ') . '08:00:00');

		$i = 30;
		$start = (int)($start / 1200) * $i ;
		$start = $start > 0 ? $start : 0;
		echo $start;
		while($start --) {
			if (!fgets($dataFp)) {
				exit();
			}
		}

		$ip = rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255);
		while($i --) {
			$ch = curl_init();
			$line = rtrim(fgets($dataFp));
			if (!$line) {
				exit();
			}
			$data = explode(",", $line);
			sleep(rand(10,30));
			if ($this->login($data[0], $data[1], $ch, $ip)) {	
				$file = fopen('yangVote', 'a+');
				fputs($file, date('Y-m-d H:i:s') . ',' . $data[0] . ',' . $data[1] . ',');
				$this->vote($ch, $file, $ip);
				fputs($file,"\n");
				fclose($file);
				curl_close($ch);
			}
		}
	}

	function login($account, $password, &$ch, $ip) 
	{
		$loginUrl = "https://www.yiban.cn/login/doLoginAjax";
		$headers = array(
			"CLIENT-IP:$ip",
			"X-Forward-For:$ip",
			"User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36",
			);
		$postData = array('account' => $account, 'password' => $password, 'captcha' => '');
		$opt = array(
			CURLOPT_URL            => $loginUrl,
			CURLOPT_HEADER         => 0,
			CURLOPT_TIMEOUT  => 20,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_COOKIEJAR      => "",
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postData
			);
		curl_setopt_array($ch, $opt);
		$res = curl_exec($ch);

		$res = json_decode($res, true);
		if ($res['code'] != 200) {
			$file = fopen('yibanLoginFail', 'a');
			fputs($file, $account . "," . $password . "," . $res['message'] . "\n" );
			fclose($file);
			return false;
		}
		return true;
	}

	function vote(&$ch, &$fileHandle, $ip) 
	{
		$voteUrl = "http://q.yiban.cn/vote/insertBoxAjax";
		$othersIds = array(
			40533,40535,40543,40545,40547,
			40549,40551,40553,40555,40557,40559,40561,40563,
			40629,40631,40633,40635,40637,40703,40707,40709,
			40775,40777,40779,40781,40783,40785,40787,40789,
			40791,40793,40797,40801,40869,40871,40875,40877,
			40879,40881,40883,40885,40887,40889);
		$randKeys = array_rand($othersIds, 7);
		foreach ($randKeys as $value) {
			$voteOptionIds[] = $othersIds[$value];
		}
		$voteOptionIds[] = 40537;//我们的id
		$voteOptionIds[] = 40539;
		$voteOptionIds[] = 40541;
		shuffle($voteOptionIds);
		fputs($fileHandle, json_encode($voteOptionIds));
		$headers = array(
			"CLIENT-IP:$ip",
			"X-Forward-For:$ip",
			"User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36",
			);
		$opt = array(
			CURLOPT_URL            => $voteUrl,
			CURLOPT_HEADER         => 0,
			CURLOPT_TIMEOUT  => 20,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "App_id=20771&Vote_id=2603&VoteOption_id[]=" . implode('&VoteOption_id[]=', $voteOptionIds),
			);

		curl_setopt_array($ch, $opt);
		$res = json_decode(curl_exec($ch), true);
		fputs($fileHandle, $res['message']);
		return $res;
	}
}


