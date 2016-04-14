<?php
/**
*
*	@copyright  Copyright (c) 2015 nidaren
*	All rights reserved
*
*	file:			Auth.php
*	description:	同路人
*
*	@author nidaren
*	@license Apache v2 License
*
**/

class Data_Together
{
    function __construct() {}

	public static function insert($num, $name,$contact, $departDate, $departPlace, $arrivePlace, $waitingTime, $pc=0)
	{
		$db = Vera_Database::getInstance();
		$conds = array('num'=> $num);
		$data = array(
			'num'          => $num,
			'name'         => $name,
			'contact'      => $contact,
			'depart_date'  => $departDate,
			'depart_place' => $departPlace,
			'arrive_place' => $arrivePlace,
			'waitingtime'  => $waitingTime,
			'pc' => $pc
		);

        $insert = $data;
        $update = $data;
        //利用MySQL特性 ON DUPLICATE KEY UPDATE，当违反num的unique时，使用update
		return $db->insert('wap_Together', $insert, NULL, $update);
	}

	public static function getInfo($num)
	{
		$db = Vera_Database::getInstance();
		$ret = $db->select('wap_Together', '*', array('num' => $num));

		if($ret) {
			$ret = $ret[0];
			$waitingTime = strtotime($ret["waitingtime"]);
			$departDate = strtotime($ret["depart_date"]);
			$tem = explode(" ", $ret["depart_date"]);
			$ret["waitingtime"] = ($waitingTime - $departDate) / 60;
			$ret["depart_date_date"] = $tem[0];
			$ret["depart_date_time"] = $tem[1];
			return $ret;
		}
		return array();
	}

	public static function getList($from, $to, $when, $wait)
	{
		$db = Vera_Database::getInstance();
		$conds = "`depart_place`='{$from}' AND `arrive_place`='{$to}' AND `waitingtime`>'{$when}' AND `depart_date`<'{$wait}' AND `pc`=0"  ;
		$appends = " order by `depart_date` asc";
		$ret = $db->select('wap_Together', '*', $conds, NULL, $appends);
		for ($i=0;isset($ret[$i]);$i++)//去掉出发时间、等待时间中的日期
		{
			$tem = explode(" ", $ret[$i]["depart_date"]);
			$ret[$i]["depart_date"] = $tem[1];
			$tem = explode(" ", $ret[$i]["waitingtime"]);
			$ret[$i]["waitingtime"] = $tem[1];
		}
		return $ret;
	}

	/**用学号密码获取手机号及姓名，现已废弃
	 * @param $num
	 * @param $pwd
	 * @return array
	 * @throws Exception
	 */
	public static function getNameTel($num,$pwd)
	{
		$cache = Vera_Cache::getInstance();
		$key = 'together_'.$num;
		$value = $cache->get($key);
		if (isset($value['contact']) && isset($value['name'])) {
			$ret = array(
				'name'    => $value['name'],
				'contact' => $value['contact']
			);
			return $ret;
		}

		Vera_Autoload::changeApp('wechat');
		$handle = Data_Xmu_Jwc::getLoginHandle($num,$pwd);
		$ret = Data_Xmu_Jwc::getInfo($handle);
		Vera_Autoload:: reverseApp();
		if(isset($ret["sjh"]))
		{
			$ret = array('name' => $ret["xm"],'contact' => $ret["sjh"]);
		}
		else
		{
			$ret = array('name' => $ret["xm"],'contact' => '');
		}
		$cache->set($key, array_merge($value,$ret), 86400);
		return $ret;
	}
}
?>
