<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Index.php
*	description:	网薪换实物入口
*
*	@author Nili
*	@license Apache v2 License
*	
**/


class Action_Index extends Action_Base
{
    function __construct() {}

    public function run()
    {

        //display网薪换实物的首页
        $view = new Vera_View(true);//设置为true开启debug模式
        $resource = $this->getResource();//resource里是用户信息

        $data = new Data_Db();
        $newestList = array('auction' => array(), 'exchange' => array());
        $newestList['auction'] = $data->getItemsList("startTime",0);//获取最新商品
        if ($newestList['auction']){
            foreach ($newestList['auction'] as $key => $value) {
                if ($value['state'] == 1)
                {
                    $newestList['auction'] = $value;
                    break;
                }
            }
        }
        $newestList['exchange'] = $data->getItemsList("startTime",1);//获取最新商品
        if ($newestList['exchange']){
            foreach ($newestList['exchange'] as $key => $value) {
                if ($value['state'] == 1)
                {
                    $newestList['exchange'] = $value;
                    break;
                }
            }
        }
        $hotList  = Service_HotItem::getHotItemList();//从cache里获取        


        $view->assign("personInfo",$resource);
        $view->assign("newestList",$newestList);
        $view->assign("hotList",$hotList);
        $view->display('mall/Index.tpl'); 
    }
}

?>