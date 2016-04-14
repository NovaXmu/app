<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Info.php
*    description:     会场签到相关信息封装
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
* Service层封装
*/
class Service_Info
{
    function __construct() {}

    /**
     * 检查该活动是否属于某用户管理且是否已通过审核
     * @param  string  $act 活动token
     * @return boolean
     */
    public static function isActBelong($act, $num)
    {
        $info = Data_Db::getActInfo($act);
        return $info['owner'] == $num;
    }

    /**
     * 检查该活动是否已通过审核
     * @param  string  $act 活动token
     * @return boolean
     */
    public static function isActPassed($act)
    {
        $info = Data_Db::getActInfo($act);
        return $info['isPassed'] == 1;
    }

    public static function getCheckinList($act)
    {
        return Data_Db::getCheckinList($act);
    }

    /**
     * 获取活动详细信息
     * @param  string $act 活动token
     * @return array      活动信息
     */
    public static function getActInfo($act)
    {
        return Data_Db::getActInfo($act);
    }

    public static function addAct($name, $start, $end, $refresh, $extra = '')
    {
        return Data_Db::setAct($owner, $name, $start, $end, $refresh, $extra);
    }

    public static function updateAct($name, $start, $end, $refresh, $extra, $md5)
    {
        return Data_Db::setAct($owner, $name, $start, $end, $refresh, $extra, $md5);
    }
}
 ?>
