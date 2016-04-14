<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Pay.php
*   description:      扫码得网薪刷新Token接口(临时)
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

// @temp:
class Action_Api_Pay extends Action_Base
{
    function __construct() {}

    public function run()
    {
        // cms登录之后，设置$_SESSION['culture']为所属的id
        if (!isset($_SESSION['culture'])) {
            return true;
        }
        $id = $_SESSION['culture'];

        $token = md5(mt_rand(1000,9999));
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_temptoken_'.$id;
        $cache->set($key, $token);

        $ret = array('errno' => '0', 'token' => $id . '&' . $token);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

}
?>
