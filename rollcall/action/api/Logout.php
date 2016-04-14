<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Logout.php
*    description:     退出会场签到管理面板登录
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
* 退出登录Api
*/
class Action_Api_Logout extends Action_Base
{
    function __construct(){}

    public function run()
    {
        session_destroy();
        header("Location: /");
        return true;
    }
}
 ?>
