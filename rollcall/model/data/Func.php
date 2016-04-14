<?php
/**
 *
 *    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
 *    All rights reserved
 *
 *    file:            Func.php
 *    description:     会场签到Data层功能封装
 *
 *    @author Yuri <zhang1437@gmail.com>
 *    @license Apache v2 License
 *
 **/

/**
* 功能封装
*/
class Data_Func
{
    function __construct() {}

    /**
     * 执行签到
     * @param  string $act 活动token
     * @param  int $num 学工号
     * @return int      签到排名，失败返回false
     */
    public static function checkin($act, $num)
    {
        $item = array('num' => $num, 'time' => date("Y-m-d H:i:s", time()));
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_' . $act . '_list';
        $rank = false;
        do {
            $list = $cache->get($key, NULL, $cas);//使用Memcached特性cas，保证高并发时的准确性
            if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
                if ($list = Library_File::load($act)) {
                    if (!$rank = self::getRank($list, $num)) {
                        $list[] = $item;
                        $rank = count($list);
                    }
                    $newList = $list;
                } else {
                    $newList = array( $item );//两层Array
                    $rank = 1;
                }
                $cache->add($key, $newList, 86400);//原子性的插入，签到记录的缓存保存二十四小时
            }
            else {
                if (!$rank = self::getRank($list, $num)) {
                    $list[] = $item;
                    $cache->cas($cas, $key, $list);
                    $rank = count($list);
                }
            }
        } while ($cache->getResultCode() != Memcached::RES_SUCCESS);

        return $rank;
    }

    public static function getRank($list, $num)
    {
        $rank = 1;
        foreach ($list as $each) {
            if ($each['num'] == $num) {
                return $rank;
            }
            $rank++;
        }
        return false;
    }
}
?>
