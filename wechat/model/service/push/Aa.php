<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Aa.php
*    description:     推送平台功能封装
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  推送平台
*/
class Service_Push_Aa
{
    private static $_resource = NULL;

    function __construct($resource = '')
    {
        self::$_resource = $resource;
    }

    /**
     * 推送给某一用户
     * @param   string $openid  openid
     * @param  array $content  推送内容
     * @return  bool           推送结果
     */
    public function pushToUser($openid, $content)
    {
        $data = new Data_Push_Func($content);
        return $data->push($openid);
    }

    public function addUserToPushList($openid, $content, $time)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'wechat_push_list';//单用户推送列表

    }

    /**
     * 推送给所有可以推送的人
     * @param   array $content  推送内容
     */
    public function pushToAll($content)
    {
        $data = new Data_Push_Func($content);
        $list = Library_List::getRecent();

        $result = $data->pushList($list);
        Library_List::addLog($content, $result);
        return $result;
    }

    /**
     * 完成定时推送任务
     * @return  bool  推送结果
     */
    public function doPushTask()
    {
        $data = new Data_Push_Db();
        $tasks = $data->getNowTasks(600);//取未来十分钟的任务
        if (!$tasks) {
            // Library_List::addLog(time(), '当前无可推送的任务');
            return true;
        }
        foreach ($tasks as $task) {
            $content = json_decode($task['content'],true);
            $result = $this->pushToAll($content);
            foreach ($result as $each) {//逐个检查结果，只要有人推送成功就认定这次任务成功
                if ($each['result']['errcode'] == 0) {
                    $data->setState($task['id'], 1);
                    break;
                }
            }
        }
        return true;
    }

}

?>
