<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Luck.php
*   description:      大屏幕抽奖页面(临时)
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

/**
* @temp: 将全部用户列表传至前端
*/
class Action_Luck extends Action_Base
{
    static $actName = array(//活动名
        '"厦门大学"官微',
        '青春厦大',
        '厦大就业指导中心',
        'i厦大',
        '新传说',
        '厦大经院学生会',
        '厦大管院学生宣传中心',
        '景润小学',
        '厦大石语',
        'FL工作室'
    );

    function __construct() {}

    public function run()
    {
        $list = '';
        $count = count(self::$actName);
        for ($i=0; $i < $count; $i++) {
            $file = SERVER_ROOT.'data/temp/'.$i.'.data';
            if (file_exists($file)) {
                $list.= file_get_contents($file);
            }
        }
        $list = substr($list, 0, strlen($list)-1);

        $view = new Vera_View(true);//设置为true开启debug模式
        $view->assign('list', $list);
        $view->display('rollcall/Luck.tpl');
        return true;
    }
}
?>
