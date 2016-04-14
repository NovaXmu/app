<?php
/**
* @copyright
*
* file:     Auction.php
* description: 投票Service层
*
* @author linjun
*/
class Service_Auction{

	private static $resource = NULL;

	function __construct($resource = NULL){
		self::$resource = $resource;
	}

    /**
    * 获取正在进行的投票活动列表信息
    * @return array
    */
    public function getNowActList(){
    	$data = new Data_Db(self::$resource);
    	$list = $data->getNowActList();
    	if(!$list){
			throw new Exception("没有正在进行的投票活动", 4201);
    	}

    	$ret = array();
    	foreach($list as $next){
    		$ret[] = $this->getActInfo($next);
    	}
    	return $ret;
    }

    /**
    * 获取已结束的投票活动列表信息
    * @return array
    */
    public function getOverActList(){
    	$data = new Data_Db(self::$resource);
    	$list = $data->getOverActList();
    	if(!$list){
			throw new Exception("没有过期的投票活动", 4201);
    	}

    	$ret = array();
    	foreach($list as $next){
    		$ret[] = $this->getActInfo($next);
    	}

    	return $ret;
    }

	/**
	* 获取投票活动详情
	* @return array
	*/
	public function getActInfo(){

		$actID = self::$resource['actID'];
		$cache = Vera_Cache::getInstance();
		$key = 'vote_' . $actID .'_info';
		$info = $cache->get($key);
		if($info){
			return $info;
		}

		$data = new Data_Db(self::$resource);

		$info = $data->getActInfo($actID);
		if(!$info){
			throw new Exception("投票活动不存在", 4201);
		}
		$cache->set($key, $info, time()+3600*24*30);

		return $info;
	}

	/**
	* 获取投票活动详情
	* @return array
	*/
	public function getProInfo(){
		$proID = self::$resource['proID'];
		$actID = self::$resource['actID'];
		$cache = Vera_Cache::getInstance();
		$key = 'vote_' . $actID . '_' . $proID . '_info';
		$info = $cache->get($key);

		if($info){
			return $info;
		}

		$data = new Data_Db(self::$resource);

		$info = $data->getProInfo($proID);
		if(!$info){
			throw new Exception("投票项目不存在", 4201);
		}
		$cache->set($key, $info, time()+3600*24*30);
		return $info;
	}

	/**
	* 获取当前活动下前五的投票项目详情
	* @return array
	*/
	public function getHotProList(){

		$actID = self::$resource['actID'];
		$cache = Vera_Cache::getInstance();
		$key = 'vote_' . $actID . '_hotList';
		$list = $cache->get($key);

		if(!$list){
			$data = new Data_Db(self::$resource);

			$list = $data->getHotProList($actID);
			if(!$list){
				throw new Exception("Data_Db::getHotProList error", 4201);
			}
			$cache->set($key, $list, time()+3600*24*30);
		}

		$ret = array();
		foreach($list as $next){
			$ret[] = $this->getProInfo($next);
		}
		
		return $ret;
	}

	/**
	* 获取活动的全部排名及其详情
	* @return array
	*/
	public function getProList(){

		$actID = self::$resource['actID'];
		$data = new Data_Db(self::$resource);

		$list = $data->getProList($actID);
		if(!$list){
			throw new Exception("Data_Db::getProList error", 4201);
		}
		$ret = array();
		foreach($list as $next)
			$ret[] = $this->getProInfo($next);

		return $ret;
	}


}
?>