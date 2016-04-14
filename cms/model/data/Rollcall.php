<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Rollcall.php
*	description:	会场签到信息操作类
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
*  抢票平台信息操作
*/

class Data_Rollcall
{

    function __construct() {}

    /**
     * 待审核活动列表
     * @return  array  活动数组
     */
    public static function getNeedReviewActs()
    {
        $db = Vera_Database::getInstance();
        //$cond = array('isPassed'=>0);
        $cond = 'isPassed = 0 and now() <= endTime';
        $append = 'order by startTime asc';
        $result = $db->select('rollcall_Board', '*', $cond, NULL, $append);
        if ($result)
        {
            foreach ($result as $key => $value)
            {
                $result[$key]['limitConds'] = json_decode($value['limitConds'], true);
            }
        }
        //$mysql = Vera_Database::getLastSql();
        //var_dump($mysql);
        return $result;
    }

    /**
     * 待开始活动列表
     * @return  array  活动数组
     */
    public static function getReadyActs()
    {
        $db = Vera_Database::getInstance();
        $cond = 'isPassed = 1 and startTime > \''.date("Y-m-d H:i:s").'\'';
        $append = 'order by startTime asc';
        $result = $db->select('rollcall_Board', '*', $cond, NULL, $append);
        if ($result)
        {
            foreach ($result as $key => $value)
            {
                $result[$key]['limitConds'] = json_decode($value['limitConds'], true);
            }
        }
        return $result;
        
    }

    /**
     * 正在进行的活动列表
     * @return  array  活动数组
     */
    public static function getOnGoingActs()
    {
        $db = Vera_Database::getInstance();
        $cond = 'isPassed = 1 and startTime <= now() and now() <= endTime';
        $result = $db->select('rollcall_Board', '*', $cond);
        if ($result)
        {
            foreach ($result as $key => $value)
            {
                $result[$key]['limitConds'] = json_decode($value['limitConds'], true);
            }
        }
        return $result;
    }

    /**
     * 获取已终止的活动
     * @param   int $isPassed  正常结束的活动或是审核未通过的活动
     * @return  array             活动数组
     */
    public static function getEndActs($isPassed = 1)
    {
        $db = Vera_Database::getInstance();

        $append = NULL;
        if ($isPassed == 1) {
            $cond = 'isPassed = 1 and now() >= endTime';//已正常结束的活动
            $append = 'order by endTime desc limit 0,5';//显示最新的五个
        }
        else {
            $cond = array('isPassed' => -1);//审核未通过的活动
            $append = 'order by id desc limit 0,5';
        }

        $result = $db->select('rollcall_Board', '*', $cond, NULL, $append);
        if ($result)
        {
            foreach ($result as $key => $value)
            {
                $result[$key]['limitConds'] = json_decode($value['limitConds'], true);
            }
        }
        return $result;
    }

    /**
     * 设置审批结果
     * @param  varchar  $md5     活动 id
     * @param integer $isPassed  审批结果，-1未通过，0为审核，1审核通过
     */
    public static function setPass($md5, $isPassed = 1)
    {
        //cache里也要审核成功
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $md5 . '_info';
        $update = $cache->get($key);
        $update['isPassed'] = $isPassed;
        $cache->set($key, $update, strtotime($update['endTime']) + 3600);//活动详情的缓存多保留一小时

        $db = Vera_Database::getInstance();

        $row = array('isPassed'=>$isPassed);
        $cond = array('md5'=>$md5);
        return $db->update('rollcall_Board', $row, $cond);
    }
   
    /**
     * 添加活动或修改活动
     * @param  varchar  $owner      学号
     * @param varchar   $name       活动名称
     * @param varchar   $start      活动开始时间
     * @param varchar   $end        活动结束时间
     * @param varchar   $refresh    二维码刷新间隔
     * @param varchar   $extra      活动额外信息
     * @param int       $award      申请网薪
     * @param string    $limitConds 限制条件
     * @param varchar   $md5        有则修改活动，无则添加活动
     */
    public static function setAct($owner, $name, $start, $end, $extra, $award, $limitConds, $md5 = NULL)
    {
        if ($md5 == NULL) { //如果需要修改活动，务必携带活动token作为md5
            $md5 = md5($owner . $name . $start . $end);
        }
        
        $update = array(
            'owner' => $owner,
            'name' => $name,
            'startTime' => $start,
            'endTime' => $end,
            'extra' => $extra,
            'award' => $award,
            'limitConds' => $limitConds,
            'isPassed' => 0
        );
        $insert = array_merge($update, array('md5' => $md5));

        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $md5 . '_info';
        $cache->set($key, $update, strtotime($update['endTime']) + 3600);//活动详情的缓存多保留一小时

        //利用MySQL特性 ON DUPLICATE KEY UPDATE，当违反md5的unique时，使用update
        //保证md5唯一并且自始至终都不变
        $db = Vera_Database::getInstance();
        $row = $db->insert('rollcall_Board',$insert,NULL,$update);
        $ret = array('errno'=>'0','errmsg'=>'OK');
        if (!$row) {
            $ret = array('errno'=>'020102','errmsg'=>'提交出错');
        }
        return $ret;
    }

    /**
     * 获取活动详细信息
     * @param  string $act 活动token
     * @return array      活动信息
     */
    public static function getActInfo($act)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $act . '_info';
        if ($ret = $cache->get($key)) {
            if(isset($ret['isPassed']))
            {
                return $ret;
            }
        }

        $db = Vera_Database::getInstance();
        $info = $db->select('rollcall_Board', '*', array('md5'=>$act));
        if (!$info) {
            return false;
        }
        $info = $info[0];
        $ret = array(
            'owner' => $info['owner'],
            'name' => $info['name'],
            'start' => $info['startTime'],
            'end' => $info['endTime'],
            'extra' => $info['extra'],
            'award' => $info['award'],
            'limitConds' => json_decode($info['limitConds'], true),
            'isPassed' => $info['isPassed']
            );

        $cache->set($key, $ret, strtotime($info['endTime']) + 3600);//活动详情的缓存多保留一小时
        return $ret;
    }

    /**
     * 获取签到结果
     * @param  string $act 活动token
     * @return array      签到结果数组
     */
    public static function getCheckinList($act)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $act . '_list';
        $list = $cache->get($key);
        if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
            if (!$list = self::load($act)) {
                return false;
            }//无需set载入的结果，有人checkin的时候自然会set
        }
        return $list;
    }

    /**
     * 载入签到结果
     * @param  string $fileName 活动token
     * @return array           签到结果数组
     */
    public static function load($fileName)
    {
        $dir = SERVER_ROOT . 'data/rollcall/%s.data';
        $file = sprintf($dir, $fileName);
        if (!file_exists($file)) {
            return false;
        }
        $content = file_get_contents($file);
        return unserialize($content);
    }
}
?>