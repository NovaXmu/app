<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			Aa.php
*	description:	抢票平台Service层
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 抢票平台Service
*/
class Service_Ticket_Aa
{
	private static $resource = NULL;

	function __construct($_resource)
	{
		self::$resource = $_resource;
	}

	public static function getList()
	{
		Vera_Autoload::changeApp('ticket');
		$class = new Service_Info(self::$resource);
		$list = $class->getList();
		Vera_Autoload::reverseApp();

		$ret['type'] = "news";
		$temp['Articles'] = array();

		$temp['Articles'][0]['Title'] = "抢票平台(升级中)";
		$temp['Articles'][0]['PicUrl'] = "http://www.novaxmu.cn/templates/ticket/img/cover.jpg";//大图
		$temp['Articles'][0]['Url'] = "";

		//$temp['Articles'][0]['Url'] = $list[0]['link'] . self::$resource['FromUserName'];
		// for($i=0;$i < count($list);$i++)
		// {
		// 	$startTime = strtotime($list[$i]['startTime']);
		// 	$startTime = date("m月d日 H:i:s", $startTime);
		// 	$temp['Articles'][$i+1]['Title'] = $list[$i]['name']."\n{$startTime}开始";
		// 	//$temp['Articles'][$i+1]['Description'] ="共{$list[$i]['total']}张票，每人限抢{$list[$i]['times']}次";
		// 	$temp['Articles'][$i+1]['Url'] = $list[$i]['link']. self::$resource['FromUserName'];//拼入 openid
		// }
		// 
		$temp['Articles'][1]['Title'] = "系统正在升级中，请稍后再试";//图文信息大标题
        $temp['Articles'][1]['Url'] = "";
		$ret['data'] = $temp;
		return $ret;
	}
}

?>
