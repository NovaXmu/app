<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Data.php
*   description:      大屏幕实时数据接口(临时)
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

// @temp:
class Action_Api_Data extends Action_Base
{
    //@temp: 网络文化节活动列表
    static $actName = array(
        '"厦门大学"官微',
        '青春厦大',
        '厦大就业指导中心',
        'i厦大',
        '新传说',
        '厦大经院学生会',
        '厦大管院学生宣传中心',
        '景润小学',
        '厦大石语',
        'FL工作室',
        '厦大易班',
        'E维工作室'
    );

    function __construct() {}

    public function run()
    {
        $act = array();
        $count = count(self::$actName);
        foreach (self::$actName as $each) {
            $act[$each]['count'] = 0;
        }
        for ($i=0; $i < $count; $i++) {
            $file = SERVER_ROOT.'data/temp/'.$i.'.data';
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $act[self::$actName[$i]]['count'] = count(explode("\n", $content)) -1;
            }
        }

        $ret = array('errno' => 0, 'data' => $act);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

}
?>
