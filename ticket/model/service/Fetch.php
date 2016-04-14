<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Fetch.php
*    description:    抢票平台Service层，抢票功能
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*
*/
class Service_Fetch
{
    private static $resource = NULL;

    function __construct($_resource = NULL)
    {
        self::$resource = $_resource;
    }

    /**
     * 抢票
     * @param  int $actID   活动id
     * @return array         抢票结果
     */
    public function getTicket($actID)
    {
        $data = new Data_Db(self::$resource);

        //检查是否绑定
        if(!$data->checkLink()) {
            throw new Exception("未绑定厦大帐号", 4101);
        }

        //检查活动合法
        $actInfo = $data->getAct($actID);
        if (!$actInfo) {
            throw new Exception("活动不存在", 4102);
        }
        if(strtotime($actInfo["startTime"]) > time()) {
            throw new Exception("尚未开始抢票", 4103);
        }
        if(strtotime($actInfo["endTime"]) < time()) {
            throw new Exception("抢票已结束", 4104);
        }

        //检查是否已超过抢票次数限制
        $count = $data->countOfUser($actID);
        if ($count >= $actInfo['times']) {
            //记录日志
            $rows = $data->saveRecord($actID,-1);
            throw new Exception("已超出抢票次数限制", 4105);
        }

        //检查有无余票
        if ($data->isLeft($actInfo) <= 0) {
            //记录日志
            $rows = $data->saveRecord($actID,-1);
            throw new Exception("已无余票", 4106);
        }

        //检查是否已经抢到票
        $userInfo = $data->getUserInAct($actID);
        if ($userInfo) {
            //记录日志
            Vera_Log::addWarning('Someone trying to use fetch api.');//如果此人抢到票还发起抢票请求，说明绕过了前端的限制
            $rows = $data->saveRecord($actID,-1);
            throw new Exception("已抢到票", 4107);
        }

        //开始抢票
        $ret = array('result' => '0', 'token' => '');
        $func = new Data_Func();
        switch ($actInfo["type"])
        {
            case 'random':
                $ret = $func->random($actInfo['chance']);
                break;
            default:
                //记录日志
                $rows = $data->saveRecord($actID,-1);
                throw new Exception("系统故障", 4108);
                break;
        }

        //从缓存中减去一张票
        if ($ret['result'] == 1) {
            if (!$data->getOne($actInfo)) {
                //记录日志
                $rows = $data->saveRecord($actID);
                throw new Exception("已无余票", 4106);
            }
        }

        $data->incrementCountOfUser($actID);//消耗一次抢票机会

        //记录日志
        $rows = $data->saveRecord($actID, $ret['result'], $ret['token']);
        if (!$rows) {
            throw new Exception("数据写入失败", 4109);
        }

        return $ret;
    }
}
?>
