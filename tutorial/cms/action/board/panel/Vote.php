<?php
/**
*	@copyright
*
*	file:		Vote.php
*	description: 投票面板
*
*	@author linjun
*/

/**
*	投票面板
*/
class Action_Border_Panel_Vote{
	function __construct(){}

	public function run(){
		$view = new Vera_View(true);//设置为true 开启debug模式
		$data = new Data_Vote();
		$temp = $data->getNeedReviewActs();

		$view->assign('needReviewActs', $temp);//默认待审核活动列表
		$view->caching = Smarty::CACHING_OFF;
		$view->display('cms/panel/Vote.tpl');
		return true;
	}

}
?>