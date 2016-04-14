<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Db.php
*    description:     推送平台用数据库信息提取类
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  信息提取 Data
*/
class Data_Push_Db
{

    function __construct() {}

    /**
     * 新建一个推送任务
     * @param string $time    推送时间
     * @param array $content  推送内容,里边有 type 和 data
     */
    public function newTask($time, $content)
    {
        $content['data'] = array_change_key_case($content['data'],CASE_LOWER);//数组键名全小写，以满足推送 api 的要求
        if ($content['type'] == 'news') {
            foreach ($content['data']['articles'] as &$each) {
                $each = array_change_key_case($each,CASE_LOWER);//数组键名全小写，以满足推送 api 的要求
            }
        }
        $content = json_encode($content,true);
        $db = Vera_Database::getInstance();
        return $db->insert('wechat_PushTask',array('content'=>$content,'pushTime'=>$time,'state'=>'0','review'=>0));
    }

    /**
     * 修改任务推送时间
     * @param int    $id      任务id
     * @param string $time    推送时间
     */
    public function updateTime($id, $time)
    {
        $db = Vera_Database::getInstance();
        $row = array('pushTime'=>$time);
        return $db->update('wechat_PushTask', $row, array('id'=>$id));
    }

    /**
     * 删除一个推送任务
     * @param  int $id 任务 id
     * @return int      影响的行数
     */
    public function delTask($id)
    {
        $db = Vera_Database::getInstance();
        return $db->delete('wechat_PushTask', array('id' => $id));
    }

    /**
     * 设置任务执行状态
     * @param  int  $id       任务 id
     * @param integer $state  状态
     */
    public function setState($id, $state = 1)
    {
        $db = Vera_Database::getInstance();
        $row = array('state' => $state);
        $db->update('wechat_PushTask', $row, array('id' => $id));
    }

    /**
     * 设置任务审核状态
     * @param  int  $id        任务 id
     * @param integer $review  审核结果
     */
    public function setReview($id, $review = 0)
    {
        $db = Vera_Database::getInstance();
        $row = array('review' => $review);
        $db->update('wechat_PushTask', $row, array('id' => $id));
    }

    /**
     * 取一个任务的信息
     * @param   int $id  任务 id
     * @return array      任务信息
     */
    public function getTask($id)
    {
        $db = Vera_Database::getInstance();
        $result = $db->select('wechat_PushTask','*',array('id'=>$id));
        if (!$result) {
            return false;
        }
        return $result[0];
    }

    /**
     * 获取任务
     * @param  integer $state    执行状态
     * @param  integer $review   审核状态
     * @param  integer $limit    显示的行数
     * @return  array  任务数组
     */
    public function getTasks($state = 0, $review = 0, $limit = 20)
    {
        $db = Vera_Database::getInstance();
        $append = 'order by pushTime desc';
        if ($state == 1 || $review == -1) { //若获取已推送过的或审核不通过的任务，只显示前$limit条
            $append.= ' limit 0,'. $limit;
        }

        if ($review == 1) { //只有审核通过的才区分状态
            $result = $db->select('wechat_PushTask','*',array('state'=>$state,'review'=>'1'),NULL,$append);
        }
        else { //未审核和审核未通过这两种忽视推送状态，反正也没法推...
            $result = $db->select('wechat_PushTask','*',array('review'=>$review),NULL,$append);
        }
        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * 取未来一段时间的任务，未推送且已审核通过
     * @param  integer $seconds 未来一段时间（秒）
     * @return  array            任务数组
     */
    public function getNowTasks($seconds = 600)
    {
        $db = Vera_Database::getInstance();
        $next = date("Y-m-d H:i:s", time() + $seconds);
        $cond = "pushTime <= '{$next}' and state = 0 and review = 1";//取出未来一段时间以内没有推送过的任务
        $result = $db->select('wechat_PushTask', '*', $cond);
        if (!$result) {
            return false;
        }
        return $result;
    }
}

?>
