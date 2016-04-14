<?php
/**
*	@copyright
*
* 	file:    Vote.php
*	description: 投票相关处理
*
*	@author linjun
*/
class Action_Api_Vote extends Action_Base{

	function __construct($resource){
		parent::__construct($resource);
	}

	public function run(){
		echo 'success vote';
		if(!isset($_GET['m'])){
			return false;
		}
		switch($_GET['m']){
			case 'vote':
				return self::_vote();
				break;
			case 'unvote':
				return self::_unvote();
				break;
			default:
				return false;
				break;
		}
	}

	private function _vote(){
		$resource = $this->getResource();
		$service = new Service_Vote($resource);

		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array()
			);

		try{
			$data = $service->vote();
			$ret['data'] = $data;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}
		// $view = new Vera_View(true);//设置为true开启debug模式
  //       $view->assign("result",$ret);
  //       $view->display('vote/result.tpl');

	}

	private function _unvote(){
		$resource = $this->getResource();
		$service = new Service_Vote($resource);

		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array()
			);

		try{
			$data = $service->unvote();
			$ret['data'] = $data;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}
		// $view = new Vera_View(true);//设置为true开启debug模式
  //       $view->assign("result",$ret);
  //       $view->display('vote/result.tpl');
	}
}
?>