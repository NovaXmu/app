<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Auction.php
*	description:	竞价api
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Api_Auction extends Action_Base
{		

    function __construct() {}

	public function run (){
    	//判断参数
    	if(isset($_GET['m']))
    	{
    		switch ($_GET['m']) {
    			case 'auction2':
    				return $this->auction();
    				break;
    			case 'detail':
    				return $this->detail();
    				break;
    		}
    	}
    	$ret = array('errno' => '1','errmsg' => '参数不对');
    	echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
    }
		
    private function auction (){
		if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['price']) || !is_numeric($_GET['price']))
        {
        	$res = array('errno'=>'1', 'errmsg' => '参数不对');
        	echo json_encode($res, JSON_UNESCAPED_UNICODE);
            return false;
        }
    	$resource = $this->getResource();
		$service = new Service_Auction();
		$res = $service->auction($resource, $_GET['id'], $_GET['price']);//用户信息,商品id,出价
        Vera_Log::addNotice('yb_userid', $resource['yb_userid']);
        Vera_Log::addNotice('auctionRes', json_encode($res, JSON_UNESCAPED_UNICODE));
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function detail(){
        if(!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
            return false;
        }
        $id = $_GET['id'];
    	$service = new Service_Auction();
        $service->checkEndAuctionToken($id, $this->getResource());//获取详情之前检查竞价情况
		Data_Cache::addItemViewed($id);//浏览量+1
        Data_Cache::setHotItem($id);
        return true;
    }
}
?>