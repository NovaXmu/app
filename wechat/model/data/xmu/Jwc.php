<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			Jwc.php
*	description:	教务系统Data层
*
*	@author Yuri
*	@license Apache v2 License
*
**/
/**
* 教务系统Data层处理类
*/
class Data_Xmu_Jwc extends Data_User
{

	function __construct($resource)
	{
		parent::__construct($resource);
	}

	/**
	*	登录验证获取登录cookie
	*
	*	@param int 学号
	*	@param string 密码
	*	@return mixed 成功时返回句柄，失败时false
	*/
	public static function getLoginHandle($num, $password)
	{
		$post_data = "Login.Token1=".$num;
		$post_data.= "&Login.Token2=".$password;

		$handle = curl_init();

		$options = array(
					CURLOPT_URL            => 'http://idstar.xmu.edu.cn/amserver/UI/Login',
					CURLOPT_HEADER         => 0,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_COOKIEJAR      => "",
					CURLOPT_POST           => 1,
					CURLOPT_POSTFIELDS     => $post_data
		            );

		curl_setopt_array($handle, $options);

		curl_exec($handle);//执行

		if(curl_getinfo($handle,CURLINFO_HTTP_CODE) == 302)//302说明验证成功
			return $handle;

		return false;
	}

	/**
	 * 获取学生个人信息(学工系统)
	 * @param  handle $handle 登录后的句柄
	 * @return array         个人信息
	 */
	public static function getInfo($handle)
	{
		if ($handle === false) {
			Vera_Log::addWarning('getInfo handle is false');
			return false;
		}
		$api = "http://xg.xmu.edu.cn/epstar/app/getxml.jsp";
		$post_data = "mainobj=SWMS/XSJBXXGLZXT/JBXX/T_JBXX_JBXX&Fields=T_JBXX_JBXX:&Filter=T_JBXX_JBXX:1=1&OrderBy=T_JBXX_JBXX:&CheckFP=no&undefined";

		$options = array(
					CURLOPT_URL            => $api,
					CURLOPT_POST           => 1,
					CURLOPT_POSTFIELDS     => $post_data
		            );
		curl_setopt_array($handle, $options);
		$content = curl_exec($handle);
		if(!($content = curl_exec($handle)))
			throw new Exception("抱歉学工系统出现了问题，请稍后再试。", 1);
		$content = (array)simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOBLANKS | LIBXML_NOCDATA);
		if (!isset($content['record'])) {
			return array();
		}
		$content = (array)$content['record'];
		$ret = array();
		foreach ($content as $key => $value) {
			if (is_object($content[$key])) {
				continue;
			}
			$key = strtolower($key);
			$ret[$key] = $value;
		}
		unset($ret['@attributes']);
		return $ret;
	}

	/**
	 * 获取学生成绩
	 * @return array     成绩数组
	 */
	public function getGrades()
	{
		$num = $this->getStuNum();
		$password = $this->getStuPass();
		$islink = $this->isLink();
		if (!$islink || $num == false || $password == false) {
			throw new Exception("请绑定厦大学工号。", 1);
		}

		$handle = self::getLoginHandle($num, $password);
		if(!$handle) {
			throw new Exception("抱歉教务系统出现了问题，请稍后再试。", 1);
		}

		$url = "http://ssfw.xmu.edu.cn/cmstar/index.portal?.pn=p1201_p3535";
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_URL, $url);
		if(!($content = curl_exec($handle)))
			throw new Exception("抱歉教务系统出现了问题，请稍后再试。", 1);

		$content = str_replace("&nbsp;", "", $content);
		$content = str_replace("\r\n", "", $content);
		$content = str_replace("\t", "", $content);
		$content = strip_tags($content,"<table><th><td>");//初步去掉html标签

		$match = "/<table.*?>.*?<\/table>/is";
		preg_match_all($match, $content, $result);//匹配出成绩表格

		$arr = explode("</th>", $result[0][1]);//区分不同学期

		$match = "/<td.*?>.*?<\/td>/is";
		preg_match_all($match, $arr[9], $arr);//arr[9]取最新学期，匹配各项成绩
		$arr = $arr[0];

		//拼装成绩数组
		$ret = array();
		for ($i=0,$j = 0; $i < count($arr); $i+=7, $j++)
		{
			$ret[$j]['name'] =trim(strip_tags($arr[$i]));//课程名称
			$ret[$j]['credit'] = trim(strip_tags($arr[$i+1]));//学分/学时
			$ret[$j]['type'] = trim(strip_tags($arr[$i+2]));//课程类型
			$ret[$j]['need'] = trim(strip_tags($arr[$i+3]));//修读性质(必修？选修？)
			$ret[$j]['score'] = trim(strip_tags($arr[$i+4]));//成绩
			$ret[$j]['reason'] = trim(strip_tags($arr[$i+5]));//特殊原因
			$ret[$j]['rank'] = trim(strip_tags($arr[$i+6]));//排名站位
		}

		if (empty($ret)) {
			throw new Exception("未查询到成绩。", 1);
		}
		return $ret;
	}

	/**
	*	获取校园卡信息
	*
	*	@param 无
	*	@return 返回信息数组
	*/
	public function getMoney()
	{
		$num = $this->getStuNum();
		$password = $this->getStuPass();
		$islink = $this->isLink();
		if (!$islink || $num == false || $password == false) {
			throw new Exception("请绑定厦大学工号。", 1);
		}

		$handle = self::getLoginHandle($num, $password);
		if(!$handle) {
			throw new Exception("抱歉教务系统出现了问题，请稍后再试。", 1);
		}

		$url = "http://i.xmu.edu.cn/index.portal";//查询页面

		$options = array(
					CURLOPT_URL            => $url,
					CURLOPT_HEADER         => 0,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_POST           => 0
		            );
		curl_setopt_array($handle, $options);

		$content = curl_exec($handle);

		if (curl_errno($handle))//检查是否有误
		{
			throw new Exception("抱歉学工系统出现了问题，请稍后再试。", 1);
		}

		$match = "/卡.*?元/";//老师
		preg_match_all($match, $content, $ret);
		$ret = $ret[0][0];
		$ret = strip_tags($ret);
		if(!empty($ret))
			return '您的' . $ret;
		return '';
	}

}
?>
