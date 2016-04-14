<?php
/**
* @copyright
*
*	file:     Info.php
*	description: 活动信息
*
*	@author linjun
*/
class Action_Test extends Action_Base{
	function __construct($resource){
		parent::__construct($resource);
		echo "success construct";
	}

	public function run(){
		echo "success run";
		if(!isset($_GET['m'])){
            return $this->_getActDetail();
        }
        else{
            switch($_GET['m']){
                case 'acthot':
                    return $this->self::_getActDetail();
                    break;
                case 'actall':
                    return $this->_getActAllPro();
                    break;
            }
        }

	}

	private function _getActDetail(){
		$resource = $this->getResource();
		var_dump($resource);
		$service = new Service_Auction($resource);

		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array()
			);
		try{
			$actInfo = $service->getActInfo();
			$ret['data']['actInfo'] = $actInfo;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}

		try{
			$hotPro = $service->getHotProList();
			$ret['data']['hotList'] = $hotPro;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}

		// $view = new Vera_View(true);//设置为true开启debug模式
  //       $view->assign("list",$list);
  //       $view->display('vote/actInfo.tpl');
	}

	public function _getActAllPro(){
		$service = new Service_Auction($resource);
		$resource = $this->getResource();
		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array()
			);
		try{
			$actInfo = $service->getActInfo();
			$ret['data']['actInfo'] = $actInfo;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}

		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array()
			);
		try{
			$list = $service->getProList();
			$ret['data']['proList'] = $list;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}

		// $view = new Vera_View(true);//设置为true开启debug模式
  //       $view->assign("list",$list);
  //       $view->display('vote/actInfo.tpl');
	}
}
?>