<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Ticket.php
*    description:    抢票平台Service层，票务相关
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*
*/
class Service_Ticket
{
    private static $resource = NULL;

    function __construct($_resource = NULL)
    {
        self::$resource = $_resource;
    }

    public function sign()
    {
        $token = self::$resource['token'];
        $actID = self::$resource['actID'];

        $data = new Data_Db(self::$resource);
        //检查活动合法
        $result = $data->signToken($actID, $token);
        if (!$result) {
            throw new Exception('已使用过票据或没有抢中记录', 4301);
        }
        return array();
    }

    public function unsign()
    {
        $token = self::$resource['token'];
        $actID = self::$resource['actID'];

        $data = new Data_Db(self::$resource);
        $actInfo = $data->getAct($actID);
        //检查活动合法
        $result = $data->unSignToken($actInfo, $token);
        if (!$result) {
            throw new Exception('已使用过票据或没有抢中记录', 4301);
        }
        return array();
    }

    public function exchange()
    {
        $token = self::$resource['token'];
        $actID = self::$resource['actID'];

        $data = new Data_Db(self::$resource);
        //检查活动合法
        $userID = $data->getUserID($actID, $token);
        if ($userID == 1) {
            throw new Exception('没有抢中记录', 4300);
        }
        if ($userID == 0)
        {
            throw new Exception('已兑换', 4300);
        }
        $StuNum = $data->getStuNumByUserID($userID);
        if (!$StuNum) {
            throw new Exception('获取学号遇到了错误', 4301);
        }
        return $StuNum;
    }
}
?>
