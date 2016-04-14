<?php
/**
*	@copyright
*	
*	file:  ProInfo.php
*	description:   投票项目信息
*
* 	@author linjun
*/
class Action_Api_Pro extends Action_Base{
	
	function __construct($resource){
		parent::__construct($resource);
	}

	public function run(){
		echo ' success pro';
		$resource = $this->getResource();
		$service = new Service_Auction($resource);

		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array()
			);
		try{
			$list = $service->getProInfo();
			$ret['data'] = $list;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}

		// $view = new Vera_View(true);//设置为true开启debug模式
  //       $view->assign("list",$list);
  //       $view->display('vote/proInfo.tpl');

        return true;

	}
}
?>