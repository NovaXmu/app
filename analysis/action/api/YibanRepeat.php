<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			YibanRepeat.php
*	description:	统计易班客户端发起重复请求的情况
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 		
*/
class Action_Api_YibanRepeat 
{
	public function run ()
	{
		set_time_limit(0);
		if ($_GET['m'] == 'download') {
			header('Content-Type: text/xls');
			header('Content-type:application/vnd.ms-excel;charset=utf-8');
			header("Content-Disposition: attachment;filename=\""."xxxxx.xls"."\"");
			header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
			header('Expires:0');
			header('Pragma:public');
			$service = new Service_Repeat();
			$ret = $service->getRepeatLog();
			foreach ($ret as $row) {
				$str = implode("\t", $row);
				echo $str . "\n";
			}
		} else {
			$service = new Service_Repeat();
			$ret = $service->getRepeatLog();
			foreach ($ret as $row) {
				print_r($row);
				echo "\n";
			}
		}
	}
}