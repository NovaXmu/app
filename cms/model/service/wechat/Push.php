<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Push.php
*    description:     推送平台 cms 封装
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  推送平台功能封装
*/
class Service_Wechat_Push
{

    function __construct() {}

    /**
     * 执行定时推送任务，用于cron
     */
    public function doTasks()
    {
        Vera_Autoload::changeApp('wechat');
        $data = new Service_Push_Aa();
        $data->doPushTask();
        Vera_Autoload::reverseApp();
    }

    /**
     * 推送给所有可能的人
     */
    public function toAll($content)
    {
        Vera_Autoload::changeApp('wechat');
        $service = new Service_Push_Aa();
        $service->pushToAll($content);
        Vera_Autoload::reverseApp();
    }

    /**
     * 新建一个推送任务
     * @param string $time    推送时间
     * @param array $content  推送内容
     */
    public function newTask($time, $content)
    {
        Vera_Autoload::changeApp('wechat');
        $service = new Data_Push_Db();
        $service->newTask($time, $content);
        Vera_Autoload::reverseApp();
    }

    /**
     * 修改任务推送时间
     * @param int    $id      任务id
     * @param string $time    推送时间
     */
    public function updateTime($id, $time)
    {
        Vera_Autoload::changeApp('wechat');
        $service = new Data_Push_Db();
        $service->updateTime($id, $time);
        Vera_Autoload::reverseApp();
    }

    /**
     * 删除一个推送任务
     * @param  int $id 任务 id
     * @return int      影响的行数
     */
    public function delTask($id)
    {
        Vera_Autoload::changeApp('wechat');
        $service = new Data_Push_Db();
        $service->delTask($id);
        Vera_Autoload::reverseApp();
    }

    /**
     * 重置任务状态，恢复未推送未审核
     * @param  int  $id       任务 id
     */
    public function resetTask($id)
    {
        Vera_Autoload::changeApp('wechat');
        $service = new Data_Push_Db();
        $service->setState($id, 0);
        $service->setReview($id, 0);
        Vera_Autoload::reverseApp();
    }

    /**
     * 设置任务审核状态
     * @param  int  $id        任务 id
     * @param integer $review  审核结果
     */
    public function setReview($id, $review = 0)
    {
        Vera_Autoload::changeApp('wechat');
        $service = new Data_Push_Db();
        $service->setReview($id, $review);
        Vera_Autoload::reverseApp();
    }

    /**
     * 获取任务
     * @param  integer $state    任务执行状态
     * @param  integer $limit    显示的行数
     * @return  array  任务数组
     */
    public function getTasks($state = 0, $review = 0, $limit = 20)
    {
        Vera_Autoload::changeApp('wechat');
        $service = new Data_Push_Db();
        $tasks = $service->getTasks($state, $review, $limit);
        Vera_Autoload::reverseApp();
        return $tasks;
    }

    /**
     * 获取待审核的任务
     * @return  array  任务数组
     */
    public function getNeedReviewTasks()
    {
        return $this->getTasks(0, 0);
    }

    /**
     * 获取已完成的任务
     * @return  array  任务数组
     */
    public function getFinishedTasks($limit = 20)
    {
        return $this->getTasks(1, 1, $limit);
    }

    /**
     * 获取准备执行的任务
     * @return  array  任务数组
     */
    public function getReadyTasks()
    {
        return $this->getTasks(0, 1);
    }

    /**
     * 获取禁用的任务
     * @return  array  任务数组
     */
    public function getDisabledTasks()
    {
        return $this->getTasks(0, -1);
    }
}

?>
