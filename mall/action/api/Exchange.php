<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Exchange.php
*	description:	兑换接口
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Action_Api_Exchange extends Action_Base
{
    function __construct() {}
	public function run (){
    	//判断参数
    	if(isset($_GET['m']))
    	{
    		switch ($_GET['m']) {
    			case 'exchange2':
    				return $this->exchange();
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

    private function detail(){
    	//判断参数
		if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
            return false;
        }
        $id = $_GET['id'];
        Service_Helper::getDetail($id);//由于addItemViewed没有检查cache为空的情况，先getDetail一下
		Data_Cache::addItemViewed($id);
        Data_Cache::setHotItem($id);
		return true;
    }

    private function exchange(){
    	//判断参数
		if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
        	$res = array('errno'=>'1', 'errmsg' => '参数不对','token' => array());
        	echo json_encode($res, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $id = $_GET['id'];
    	$resource = $this->getResource();
		$service = new Service_Exchange();
		$res = $service->exchange($resource,$id);//用户信息和商品id
		if (!$res['errno'])//有种特殊情况忽略之
        {
            Action_Api_Helper::refreshUserInfo($resource);
        }
        Vera_Log::addNotice('yb_userid', $resource['yb_userid']);
        Vera_Log::addNotice('exchangeRes', json_encode($res, JSON_UNESCAPED_UNICODE));
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
?>