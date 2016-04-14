<?php
	/**
	 * 易班开放平台SDK
	 *
	 * 单例，使用此对象初始化其它Library_Ybapi的实例对象。
	 */
	class Library_YbOpenApi
	{
		
		const YIBAN_OPEN_URL = "https://openapi.yiban.cn/";
		
		private static $mpInstance = NULL;
		
		private $_config = array(
			'appid'	 => '',
			'seckey' => '',
			'token'	 => '',
			'backurl'=> ''
		);
		
		private $_instance = array();
		
		
		/**
		 * 取Library_YbOpenApi实例对象
		 * 
		 * 单例，其它的配置参数使用init()或bind()方法设置
		 */
		public static function getInstance()
		{
			if (self::$mpInstance == NULL)
			{
				self::$mpInstance = new self();
			}
			return self::$mpInstance;
		}
		
		/**
		 * 构造函数
		 * 
		 * 使用 Library_YbOpenApi::getInstance() 初始化
		 */
		private function __construct()
		{
		}
		
		/**
		 * 初始化设置
		 *
		 * Library_YbOpenApi对象的AppID、AppSecret、回调地址参数设定
		 *
		 * @param String 应用的APPID
		 * @param String 应用的AppSecret
		 * @param String 回调地址
		 * @return Library_YbOpenApi 自身实例
		 */
		public function init($appID, $appSecret, $callback_url='')
		{
			$this->_config['appid']   = $appID;
			$this->_config['seckey']  = $appSecret;
			$this->_config['backurl'] = $callback_url;
			
			return self::$mpInstance;
		}
		
		/** 
		 * 设定访问令牌
		 *
		 * 如果已经取到访问令牌，使用此方法设定
		 * 大多的接口只需要访问令牌即可完成操作
		 * 这类接口不需要调用init()方法
		 *
		 * @param String 访问令牌
		 * @return Library_YbOpenApi 自身实例
		 */
		public function bind($access_token)
		{
			$this->_config['token']  = $access_token;
			
			return self::$mpInstance;
		}
		
		/**
		 * 站内应用辅助接口类
		 * 
		 * 可以使用该类快速便捷的进行站内应用的授权认证
		 *
		 * @return Library_Ybapi::FrameUtil
		 */
		public function getFrameUtil()
		{
			if (!isset($this->_instance['frameutil']))
			{
				assert(!empty($this->_config['appid']), Library_Lang::E_NO_APPID);
				assert(!empty($this->_config['seckey']), Library_Lang::E_NO_APPSECRET);
				assert(!empty($this->_config['backurl']), Library_Lang::E_NO_CALLBACKURL);
				$this->_instance['frameutil'] = new Library_Ybapipi_FrameUtil($this->_config['appid'],$this->_config['seckey'],$this->_config['backurl']);
			}
			return $this->_instance['frameutil'];
		}
		
		/**
		 * 授权接口功能类
		 *
		 * 通用的授权认证接口对象，可以对访问令牌进行查询回收操作
		 *
		 * @return Library_Ybapi::Authorize
		 */
		public function getAuthorize()
		{
			if (!isset($this->_instance['authorize']))
			{
				$this->_instance['authorize'] = new Library_Ybapi_Authorize($this->_config);
			}
			return $this->_instance['authorize'];
		}
		
		/**
		 * 授权接口功能类
		 *
		 * 通用的授权认证接口对象，可以对访问令牌进行查询回收操作
		 *
		 * @return Library_Ybapi::Authorize
		 */
		public function getUser()
		{
			if (!isset($this->_instance['user']))
			{
				assert(!empty($this->_config['token']), Library_Lang::E_NO_ACCESSTOKEN);
				
				$this->_instance['user'] = new Library_Ybapi_User($this->_config['token']);
			}
			return $this->_instance['user'];
		}
		
		/**
		 * 授权接口功能类
		 *
		 * 通用的授权认证接口对象，可以对访问令牌进行查询回收操作
		 *
		 * @return Library_Ybapi::Authorize
		 */
		public function getFriend()
		{
			if (!isset($this->_instance['friend']))
			{
				assert(!empty($this->_config['token']), Library_Lang::E_NO_ACCESSTOKEN);
				
				$this->_instance['friend'] = new Library_Ybapi_Friend($this->_config['token']);
			}
			return $this->_instance['friend'];
		}
		
		
		/**
		 * HTTP请求辅助函数
		 *
		 * 对CURL使用简单封装，实现POST与GET请求
		 *
		 * @param String URL地址
		 * @param Array  参数数组
		 * @param Boolean 是否使用POST方式请求
		 * @param Array   服务返回的JSON数组
		 */
		public static function QueryURL($url, $param = array(), $isPOST = false)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			if ($isPOST)
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			}
			else if (!empty($param))
			{
				$xi   = parse_url($url);
				$url .= empty($xi['query']) ? '?' : '&';
				$url .= http_build_query($param);
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			$result = curl_exec($ch);
			if ($result == false)
			{
				throw new Library_YbException(curl_error($ch));
			}
			return json_decode($result, true);
		}
		
	}
	
	

?>