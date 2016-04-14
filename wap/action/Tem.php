<?php
/**
 * 测试发网薪
 */
class Action_Tem
{
   public function run()
    {
        Vera_Autoload::setApp('wechat');
        $seviceReply = new Service_Reply_Aa(array());
        var_dump($seviceReply->temLuck('oqRAFj0JMJGEZOShabLnCmt0eKTI'));
    }
}