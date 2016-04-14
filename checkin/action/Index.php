<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			text.php
*	description:	签到页
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 签到页
*/
class Action_Index extends Action_Base
{

	function __construct() {}

	public function run()
	{

        $resource = $this->getResource();
        $model = new Service_Question($resource);
        $db = new Data_Db($resource);

        $view = new Vera_View(true);//设置为true开启debug模式
        $view->setCacheLifetime( 86400 );//缓存时间为每天

        $isCached = $view->isCached('checkin/Index.tpl', date('d'));
        $isChecked = $model->isChecked();
        Vera_Log::addNotice('isCached', intval($isCached));

 //       if (!$isCached && is_bool($isChecked)) {
            $view->clearCache('checkin/Index.tpl');//清除旧缓存


            //获取问题
            $question = $model->getQuestion();

            $view->assign('title','每日签到');
            $view->assign('id', $question['id']);
            $view->assign('content', $question['content']);
            $view->assign('option', $question['option']);
            $view->assign('optionType', $question['optionType']);
            $view->assign('questionType', $question['questionType']);
            $view->assign('remark', $question['remark']);

 //       }
        
        //var_dump($isChecked);
        if (!empty($isChecked)) {
            $pay = $db->isPay();
            if($pay < 0){
                $view->clearCache('checkin/Index.tpl');//清除旧缓存
            }
            $view->assign('isChecked', '1');
            $view->assign('order', $isChecked['order']);
            $view->assign('count', $isChecked['count']);
            $view->assign('pay', $pay);
        }else {
            $view->assign('isChecked', '0');
        }

        $view->assign('num', $resource['num'], true);//动态赋值学号
        $view->dailyBackground();
        $view->display('checkin/Index.tpl',date('d'));
        return true;
	}
}

?>
