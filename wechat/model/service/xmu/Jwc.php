<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			Jwc.php
*	description:	教务系统Service
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 教务系统Service层
*/
class Service_Xmu_Jwc
{
	private static $resource = NULL;

	function __construct($_resource)
	{
		self::$resource = $_resource;
	}

	/**
	 * 成绩查询
	 * @return array 返回值数组
	 */
	public function grades()
	{
		$ret['type'] = 'text';
		$class = new Data_Xmu_Jwc(self::$resource);
		try {
			$result = $class->getGrades();
			$temp = '';
			foreach ($result as $each) {
				$temp.= $each['name']."\n";
				$temp.='学分:'.$each['credit']."\n";
				$temp.='修读类型:'.$each['need']."\n";
				$temp.='成绩:'.$each['score']."\n";
				$temp.="\n";
			}
		} catch (Exception $e) {
			$temp = $e->getMessage();
		}

		$ret['data']['Content'] = $temp;

		return $ret;
	}
}

?>
