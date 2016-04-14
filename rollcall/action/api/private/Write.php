<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Write.php
*    description:     私有Api，用于定时将缓存中的签到名单写入文件
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
*   虽然私有，但在Auth层并没有做强制限制，本Api随意调用并无隐患
*/
class Action_Api_Private_Write extends Action_Base
{
    function __construct() {}

    public function run()
    {
        $acts = self::getActs();
        $cache = Vera_Cache::getInstance();
        foreach ($acts as $act) {
            $key = 'rollcall_' . $act['md5'] . '_list';
            if ($list = $cache->get($key)) {
                Library_File::write($act['md5'], $list);
            }
        }
        return true;
    }

    private static function getActs()
    {
        $db = Vera_Database::getInstance();
        $start = date("Y-m-d H:i:s", time() + 86400);//一天内
        $end = date("Y-m-d H:i:s", time() - 3600);//过期一小时内
        $condition = "startTime <= '{$start}'  and '{$end}' <= endTime and isPassed = 1";
        return $db->select('rollcall_Board', 'md5', $condition);
    }
}
 ?>
