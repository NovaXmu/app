<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Repeat.php
*	description:	/log/yiban/tmp文件分析易班重复请求的 问题
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Service_Repeat
{
	private static $_logDir;
	private static $_fileName;
    function __construct() 
    {
    	self::$_logDir = SERVER_ROOT . 'log/yiban/';
    }
	public function getRepeatLog ( $date = "2015-11-20")
	{
    	self::$_fileName = 'tmp.log';
		if ($date < "2015-11-20") {
			$date = "2015-11-20";
		}

		$data = new Data_Log();
		$ret = array();
		$accessHandle = fopen(SERVER_ROOT . 'log/access.log', 'r');
		$handle = fopen(self::$_logDir . self::$_fileName, 'r');
		$offset = -2000;
		$step = 10000;
		fseek($accessHandle, $offset, SEEK_END);
		fgets($accessHandle);
		while ($line = fgets($handle)) {
			$line = $data->getYibanTmpLineInfo($line);
			if (!isset($line['time']) || $line['time'] < $date) {
				continue;
			}
			$key = $line['IP'] . "_" . $line['time'];
			if (isset($ret[$key])) {
				$ret[$key]['count'] ++;
				continue;
			}
			$accessLogLine = fgets($accessHandle);
			if (!$accessLogLine) {
				fseek($accessHandle, $offset - $step, SEEK_END);//从文件尾读起，逐步向文件头读
				fgets($accessHandle);
				$accessLogLine = fgets($accessHandle);
			}
			while($accessLogLine) {
				$accessLineInfo = Data_Log::getAccessLineInfo($accessLogLine);
				if (!isset($accessLineInfo['time']) || $accessLineInfo['time'] > $line['time']) {
					fseek($accessHandle, $offset - $step, SEEK_END);//从文件尾读起，逐步向文件头读
					fgets($accessHandle);
					$accessLogLine = fgets($accessHandle);
					$step += 10000;
				} else {
					break;
				}
			}

			while ($accessLogLine) {
				$accessLineInfo = Data_Log::getAccessLineInfo($accessLogLine);
				if ($accessLineInfo['time'] == $line['time'] && $accessLineInfo['remoteIp'] == $line['IP']) {
					$ret[$key]['IP'] = $line['IP'];
					$ret[$key]['time'] = $line['time'];
					$ret[$key]['requestUrl'] = $accessLineInfo['requestUrl'];
					$ret[$key]['count'] = 1;
					$ret[$key]['user_agent'] = $accessLineInfo['user_agent'];
					$ret[$key]['param'] = $accessLineInfo['param'];
					break;
				}
				$accessLogLine = fgets($accessHandle);
			}
		}
		return $ret;
	}
}