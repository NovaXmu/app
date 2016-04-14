<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Statistics.php
*	description:	统计访问量
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Statistics
{
	
	function __construct()
	{
		
	}

	function run()
	{
		echo 'xsw';
		if (!isset($_GET['appName']) || !isset($_GET['fileName']))
		{
			return false;
		}
		switch ($_GET['fileName']) {
			case 'notice':
				return self::_countByNotice();
				break;
			
			default:
				return true;
				break;
		}
	}

	public static function _countByNotice()
	{
		$ipCount = 0;
		$pvCount = 0;
		$ipArr = array(); 
		$uvCount = 0;//uvCount待完成
		$startTime = isset($_GET['startTime']) ? $_GET['startTime'] : '2015-01-01 00:00:00';
		$readCount = floor((time() - strtotime($startTime)) / 86400) + 1 ;//估计平均一天1条记录
		if ($readCount < 0)
		{
			echo "起始时间不能晚于今天";
			return false;
		}
		echo $readCount . "天的记录<br>";

		Vera_Autoload::changeApp($_GET['appName']);
		var_dump($_GET['appName']);
		while(1)
		{
			$buffer = Vera_Log::readLog('notice', $readCount);
			$lineInfo = self::_getInfoInEachLine($buffer[0]);//0号时间最早
			if ($lineInfo['time'] < $startTime || count($buffer) < $readCount)
				break;
			$readCount += $readCount;
		}
		Vera_Autoload::reverseApp();

		foreach ($buffer as $line) {
			$lineInfo = self::_getInfoInEachLine($line);
			if ($lineInfo['time'] < $startTime)
				continue;
			$pvCount ++;//page view
			$lineDate = date("Y-m-d", strtotime($lineInfo['time']));
			$ipArr[$lineDate . '_' . $lineInfo['remoteIP']] = 1;//同一天内相同ip算一次访问
		}
		$ipCount = count($ipArr);
		echo "pvCount:" . $pvCount . "<br>" ;
		echo "ipCount:" . $ipCount;
	}
 

	/**
	* [NOTICE] time[2015-06-10 19:59:26] method[GET] remoteIP[211.97.128.238] 
	* requestURI[/mall/api/bobing?m=bobing] auth[success] yb_userid[1142289]
	*/
	public static function _getInfoInEachLine($line)
	{
		$tok = strtok($line, "[]");//去fileName,比如notice、error、warning
		for ($tok = strtok("[]"), $i = 1; $tok !== false; $tok = strtok("[]"), $i ++)
		{
			if ($i % 2)
				$key = ltrim($tok, " ");
			else
				$ret[$key] = $tok;
		}
		return $ret;
	}
}

?>