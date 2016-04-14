<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Cache.php
*	description:	缓存相关
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Data_Cache
{
	/**
	* 易班用户黑名单
	*/
	public static function addUserBlackList($yb_userid)
	{
		$cache = Vera_Cache::getInstance();
		$key = "BlackList_" . $yb_userid;
		$res = $cache->get($key);
		if (empty($res))
		{
			$res = 0;
		}
		$res ++;
		if ($res >= 3)
		{
			$cache->set($key, $res, time() + 3600*24*30);//连续三次一个月黑名单
		}
		else
		{
			$cache->set($key, $res, time() + 20);//测试时期20s后解除黑名单
		}
	}

	public static function addIPBlackList($IP)
	{
		$cache = Vera_Cache::getInstance();
		$key = "BlackList_" . $IP;
		$res = $cache->get($key);
		if (empty($res))
		{
			$res = 0;
		}
		$res ++;
		if ($res >= 3)
		{
			$cache->set($key, $res, time() + 3600*24);//连续三次一天黑名单
		}
		else
		{
			$cache->set($key, $res, time() + 20);//测试时期20s后解除黑名单
		}
	}

	public  static function checkUserLegal($yb_userid)
	{
		$keyIP = "BlackList_" . $_SERVER["REMOTE_ADDR"];
		$keyUser = "BlackList_" . $yb_userid;
		$cache = Vera_Cache::getInstance();
		if (empty($cache->get($keyUser)) && empty($cache->get($keyIP)))
		{
			return true;
		}
		return false;
	}

	/**
	 * 设置缓存中的热门商品，存商品id即可
	 * arr_hot[0] arr_hot[1] 为兑换商品
	 * @param  int      $id   某浏览量变化的商品的id
	 * @return true
	 * @author linjun   test pass
	 * @rewrite by nili
	 */
	public static function setHotItem($id)
	{
		$cache = Vera_Cache::getInstance();
		$key_hot = 'mall_hotItemID';

		$arr_hot = $cache -> get($key_hot);
		if($arr_hot){
			if (in_array($id, $arr_hot))
			{
				return true;
			}
		}
		else
		{
			$arr_hot = array(0,0,0,0);//没有缓存时的初始化
		}
		$key = 'mall_' . $id . "_info";
		$detail_now = $cache -> get($key);

		$start = (1 - $detail_now['type']) * 2;
		$end = $start + 1;

		$key = "mall_" . $arr_hot[$end] . "_info";
		$detail_end = $cache->get($key);
		$detail_end['itemViewed'] = empty($detail_end) ? 0 : $detail_end['itemViewed'];

		$key = "mall_" . $arr_hot[$start] . "_info";
		$detail_start = $cache->get($key);
		$detail_start['itemViewed'] = empty($detail_start) ? 0 : $detail_start['itemViewed'];

		//未保持$detail_start['itemViewed'] > $detail_end['itemViewed']的顺序
		if ($detail_start['itemViewed'] > $detail_end['itemViewed'] && $detail_now['itemViewed'] >= $detail_end['itemViewed'])
		{
			$arr_hot[$end] = $id;
		}
		if ($detail_end['itemViewed'] >= $detail_start['itemViewed'] && $detail_now['itemViewed'] >= $detail_start['itemViewed'])
		{
			$arr_hot[$start] = $id;
		}
		$cache->set($key_hot, $arr_hot, time()+3600*24*30);
	}


    /*
     * 获取兑换有效时间间隔，及购买商品后多少天内可兑换（设置是在cms里）
     * @return int token有效时间间隔
     * @author nili
     */
    public static function getItemEffectiveDay()
    {
        $key = "mall_itemEffectiveDay";
        $cache = Vera_Cache::getInstance();
        $effectiveDay = $cache->get($key);
        return empty($effectiveDay) ? 7 : $effectiveDay;
    }

    /**
     * 从cache里,浏览量+1，没有检查cache为空
     * @param  int     $id      商品id
     * @return true
     * @author Nili      done
     */
    public static function addItemViewed($id){
        $cache = Vera_Cache::getInstance();
        $key = "mall_" . $id . "_info" ;
        $tem = $cache->get($key);
        ++$tem['itemViewed'];
        $cache->set($key, $tem, time()+3600*24*30);//$_SERVER[‘REQUEST_TIME’]比time()更优，以后再说
        return true;
    }

    public static function getCache($key)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'mall_' . $key;
        return $cache->get($key);
    }
}
?>