<?php  
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			HotItem.php
*	description:	热门商品
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Service_HotItem
{
	
	function __construct()
	{
			
	}

	public static function getHotItemList()
	{
		$cache = Vera_Cache::getInstance();
		$key = "mall_hotItemID";
		$arr = $cache->get($key);
		$list = array();
		if (!empty($arr))
		{
			foreach ($arr as $id) {
				if (!$id)
				{
					continue;
				}
				$list[] = Service_Helper::getDetail($id);
			}
		}
		return $list;
	}
}
?>