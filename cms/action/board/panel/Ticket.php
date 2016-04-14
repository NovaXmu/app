<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Ticket.php
*    description:     抢票平台面板
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  抢票平台面板
*/
class Action_Board_Panel_Ticket
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(false);//设置为true开启debug模式
        $data = new Data_Ticket();

        $view->assign('needReview', $data->getNeedReviewActs());//待审核列表

        $view->caching = Smarty::CACHING_OFF;
        $view->display('cms/panel/Ticket.tpl');
        return true;
    }
}

?>
