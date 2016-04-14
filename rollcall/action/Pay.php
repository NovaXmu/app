<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Pay.php
*   description:      扫码得网薪页面入口(临时)
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

/**
* @temp:
*/
class Action_Pay extends Action_Base
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
        $view = new Vera_View(true);//设置为true开启debug模式
        $view->assign('name', self::$actName[intval($_SESSION['culture'])]);
        $view->assign('id', intval($_SESSION['culture']));
        $view->display('rollcall/Pay.tpl');
        return true;
    }
}
?>
