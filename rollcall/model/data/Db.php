<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Db.php
*    description:     会场签到Data层数据获取封装
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

class Data_Db
{
    function __construct() {}

    /**
     * 检查Token是否合法
     * @param  string  $act   活动token
     * @param  string  $token 当前二维码token
     * @return boolean        是否合法
     */
    public static function isTokenValid($act, $token)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $act . '_token';
        return $cache->get($key) == $token;
    }

    /**
     * 生成新的二维码token
     * @param  string $act 活动token
     * @return string      新的二维码token
     */
    public static function newToken($act)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $act . '_token';
        $token = '';
        for ($i = 0; $i < 12; $i++) {
            $token .= chr(mt_rand(48, 122));
        }
        $token = md5($token);
        $cache->add($key, $token, 15);
        if ($cache->getResultCode() == Memcached::RES_NOTSTORED) {
            $token = $cache->get($key);
        }
        Vera_Log::addNotice('token',$token);
        return $token;
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
            $ret['limitConds'] = json_decode($ret['limitConds'], true);
            return $ret;
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
            'isPassed' => $info['isPassed'],
            'award' => $info['award'],
            'limitConds' => $info['limitConds']
            );

        $cache->set($key, $ret, strtotime($info['endTime']) + 3600);//活动详情的缓存多保留一小时
        $ret['limitConds'] = json_decode($ret['limitConds'], true);
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
            if (!$list = Library_File::load($act)) {
                return false;
            }//无需set载入的结果，有人checkin的时候自然会set
        }
        return $list;
    }

    /**
     * 插入和修改活动信息
     * @param  int $owner   所有者学工号
     * @param  string $name    活动名称
     * @param  datatime $start   活动开始时间
     * @param  datatime $end     活动结束时间
     * @param  string $extra   附加信息
     * @param  string $md5     活动token
     * @return int          影响的行数
     */
    public static function setAct($owner, $name, $start, $end, $extra = '', $md5 = NULL)
    {
        if ($md5 == NULL) { //如果需要修改活动，务必携带活动token作为md5
            $md5 = md5($owner . $name . $start . $end);
        }
        $update = array(
            'owner' => $owner,
            'name' => $name,
            'startTime' => $start,
            'endTime' => $end,
            'extra' => $extra
        );
        $insert = array_merge($update, array('md5' => $md5));

        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $md5 . '_info';
        $cache->set($key, $update, strtotime($update['endTime']) + 3600);//活动详情的缓存多保留一小时

        //利用MySQL特性 ON DUPLICATE KEY UPDATE，当违反md5的unique时，使用update
        //保证md5唯一并且自始至终都不变
        return $db->insert('rollcall_Board',$insert,NULL,$update);
    }

    /**
     * 根据xmu_num获取易班相关信息
     * @param string $xmu_num 
     * @return array yiban_uid,yiban_islinked,access_token,expire_time
     * @author nili <nl_1994@foxmail.com>
     */
    public static function getYibanInfoByXmuNum($xmu_num) 
    {
        $ret = array('yiban_islinked' => 0,
            'yiban_uid' => 0,
            'access_token' => '',
            'expire_time' => '');
        $db = Vera_Database::getInstance();
        $user = $db->select('User', array('yibanUid yiban_uid', 'isLinkedYiban yiban_islinked'), array('xmuId' => $xmu_num));
        $ret['yiban_islinked'] = $user[0]['yiban_islinked'];
        $ret['yiban_uid'] = $user[0]['yiban_uid'];
        
        if ($user[0]['yiban_islinked'])
        {
            $yiban = $db->select('Yiban', '*', array('uid' => $user[0]['yiban_uid']));
            $ret['access_token'] = $yiban[0]['accessToken'];
            $ret['expire_time'] = $yiban[0]['expireTime'];
        }

        return $ret;
    }

    /**
     * 根据学号获取密码
     * @param string  $xmu_num 学号
     * @return string 密码
     * @author nili
     */
    public static function getPwdByXmuNum($xmu_num)
    {
        $db = Vera_Database::getInstance();
        $res = $db->select('User', 'xmuPassword', array('xmuId' => $xmu_num));
        return $res[0]['xmuPassword'];
    } 

}
?>
