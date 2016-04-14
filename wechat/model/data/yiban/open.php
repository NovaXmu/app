<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			open.php
*	description:	易班开放平台相关接口data类
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 接口data类
*/
class Data_Yiban_Open extends Data_User
{
	private $conf;

	function __construct($resource)
	{
		parent::__construct($resource);
		$_conf = Vera_Conf::getConf('global');
		if (!isset($_conf['yiban']) || !isset($_conf['yiban']['AppKey']) || !isset($_conf['yiban']['AppSecret'])) {
			Vera_Log::addErr('yiban global conf get failed');
			throw new Exception("", 1);
		}
		$conf = $_conf['yiban'];
	}

	/**
	*	刷新access_token
	*
	*	@param string refreshToken
	*	@return string 新的access_token
	*/
	private function refreshToken()
	{
		$token = $this->getYibanRefresh();
		$appKey = $this->conf['AppKey'];
		$api = "https://graph.yiban.cn/token.php?client_id=%s&grant_type=refresh_token&refresh_token=%s";
		$url = sprintf($api,$appKey,$token);

		$handle = curl_init();
		$options = array(CURLOPT_URL => $url,
		                 CURLOPT_HEADER => 0,
		                 CURLOPT_RETURNTRANSFER => 1
		                );
		curl_setopt_array($handle, $options);
		$result = curl_exec($handle);//执行
		if ($errno = curl_errno($handle))//检查是否有误
		{
			Vera_Log::addWarning('yiban refresh token get failed');
			return false;
		}

		$result = json_decode($result,true);
		$access_token = $result['access_token'];
		$valid_time = $result['expires_in'];
		$expire_time = date("Y-m-d H:i:s",strtotime("+" . $valid_time . "second"));

		if(!$db = Vera_Database::getInstance()) {
			Vera_Log::addWarning('Database instance get failed');
			return false;
		}
		$update = array(
				"yiban_accessToken" => $access_token,
				"yiban_validTime" => $valid_time,
				"yiban_expireTime" => $expire_time
			);
		$result = $db->update('User_info', $update, array("ID" => $this->getID()));
		if (!$result) {
			Vera_Log::addWarning('refresh token update failed');
			return false;
		}
		Vera_Log::addNotice('refreshYibanToken','1');
		return $access_token;
	}

	/**
	 * 发送易班状态
	 * @param   int $uid      易班用户 uid
	 * @param   string $content  发送的状态内容
	 * @return  bool           发送状态
	 */
	protected function sendYibanWeibo($content = '')
	{

		// $yiban = new _YibanApi($this->appKey,$this->appSecret,$this->access_token);
		// $status = iconv('gbk', 'utf-8//IGNORE', $content);
		// $yiban->pub_statuses($status,"http://wechatyiban.xmu.edu.cn/img/2weima.jpg");
		// return true;
		//$this->setUser('oqRAFj1_W9r76-1nowBKFM9rHiRk');
		if(empty($this->access_token))
			return false;
		$access_token = $this->access_token;

		//$status = mb_convert_encoding($content, 'utf-8', 'gbk');

		$status = iconv('gbk', 'utf-8//IGNORE', $content);

		//$status = urlencode($status);
		//$status = utf8_encode($content);

		$url = "https://api.yiban.cn/statuses/upload.json";
		$post_data = "access_token=". $access_token;
		$post_data.= "&status=" . $status;
		$picUrl = "http://wechatyiban.xmu.edu.cn/img/2weima.jpg";
		$post_data.= "&pic=". $picUrl;

		$handle = curl_init();
		curl_setopt($handle, CURLOPT_HEADER, 0);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_HTTPHEADER, "Content-Type: multipart/form-data");
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_POST, 1);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);

		$result = curl_exec($handle);//执行

		if ($errno = curl_errno($handle))//检查是否有误
		{
			var_dump($errno);
			return false;
		}
		return true;
	}

	/**
	 * 获取易班厦大论坛主贴列表
	 * @return array 列表
	 */
	public function getXmuPost()
	{
		$url = $this->getPostListAPI;
		$url.= "?access_token=".$this->access_token;
		$url.= "&area=314";//厦大板块Id
		$url.= "&page=1";
		$url.= "&size=9";//受限于微信图文信息长度只能是10条，留出一条大图

		$handle = curl_init();
		curl_setopt($handle, CURLOPT_HEADER, 0);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_URL, $url);

		$content = curl_exec($handle);//执行
		if (curl_errno($handle))//检查是否有误
		{
			return 404;
		}

		$result = json_decode($content,true);//json解码返回关联数组

		return $result;
	}
}
?>
