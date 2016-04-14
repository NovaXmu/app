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

class Action_Api_Helper extends Action_Base
{
    function __construct() {}
    public static function refreshUserInfo($resource)
    {
        Vera_Autoload::changeApp('yiban');
        $newUserInfo = json_decode(Data_Yiban::sendRequest("https://openapi.yiban.cn/user/real_me?access_token={$resource['access_token']}"),true);//true转换为数组，再取一次身份信息？需要吗o.o
        Vera_Autoload::reverseApp();
        $resource['yb_money'] = $newUserInfo['info']['yb_money'];//再取一次还是直接加，待定
        parent::setResource($resource);
        $_SESSION['yb_user_info']['yb_money'] = $newUserInfo['info']['yb_money'];
    }
}