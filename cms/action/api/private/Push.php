<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Push.php
*    description:     定时推送入口
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*
*/
class Action_Api_Private_Push extends Action_Base
{

    function __construct() {}

    public function run()
    {
        $conf = Vera_Conf::getAppConf('autopush');
        if ($conf['on']) {
            $service = new Service_Wechat_Push();
            $service->doTasks();
        }
    }
}
?>
