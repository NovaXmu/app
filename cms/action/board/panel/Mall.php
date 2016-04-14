<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Mail.php
*	description:	网薪换实物面板
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
*  网薪换实物面板
*/
class Action_Board_Panel_Mall
{

    function __construct() {}

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式
        $data = new Data_Mall();
        $temp = $data->getList(0);
        if ($temp)
        {
            foreach ($temp as $key => $each) {
                $each = json_decode($each['limitConds'], true);
                $temp[$key]['limitConds'] = $each;
            }
        }
        $view->assign('newItems', $temp);//默认新商品列表
        $view->caching = Smarty::CACHING_OFF;
        $view->display('cms/panel/Mall.tpl');
        return true;
    }
}

?>