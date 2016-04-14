<?php
/**
*	@copyright
*
*	file:		Vote.php
*	description: 投票Api
*
*	@author linjun
*/

/**
*	投票
*/
class Action_Api_Vote extends Action_Base{
	function __construct(){}

	public function run(){

		if(!isset($_GET['m'])){
			$ret = array('errno' => 1, 'errmsg' => 'm缺失');
			echo json_encode($ret, JSO_UNESCAPED_UNICODE);
			return false;
		}

		switch($_GET['m']){
			case 'needReview':
				return $this->_getNeedReiewActs();
				break;
			case 'ready':
				return $this->_getReadActs();
				break;
			case 'now':
				return $this->_getNowActs();
				break;
			case 'over':
				return $this->_getEndActs(1);
			case 'notPassed':
				return $this->_getEndActs(-1);
				break;
			case 'review':
				if(!isset($_GET['actID']) || !is_numeric($_GET['actID'])|| !isset($_GET['isPassed']) || !is_numeric($_GET['isPassed'])){
					return false;
				}
				return $this->_setPass($_GET['actID'], $_GETT['isPassed']);
				break;
			case 'addAct':
				return $this->_addAct();
				break;
			case 'addPro':
				return $this->_addPro();
				break;
			case 'actInfo':
				if(!isset($_GET['actID']) || !is_numeric($_GET['actID'])){
					return false;
				}
				return $this->_getActInfo($_GET['actID']);
				break;
			case 'proInfo':
				if(!isset($_GET['proID']) || !is_numeric($_GET['proID'])){
					return false;
				}
				return $this->_getProInfo($_GET['proID']);
				break;

			// case 'deleteAct':
			// 	return $this->_deleteAct();
			// 	break;
			// case 'delectPro':
			// 	return $this->_deletePro();
			// 	break;

			default:
				return false;
				break;
		}
	}

	private function _getNeedReiewActs(){

		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array();
			);

		$data = new Data_Vote();
		$temp = $data->getNeedReiewActs();

		if(!$temp){
			$ret = array('errno'=> 0, 'errmsg' => 'error');
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$ret['data'] = $temp;
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	private function _getReadActs(){
		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array();
			);

		$data = new Data_Vote();
		$temp = $data->getReadyActs();

		if(!$temp){
			$ret = array('errno'=> 0, 'errmsg' => 'error');
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$ret['data'] = $temp;
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	private function _getNowActs(){
		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array();
			);

		$data = new Data_Vote();
		$temp = $data->getNowActs();

		if(!$temp){
			$ret = array('errno'=> 0, 'errmsg' => 'error');
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$ret['data'] = $temp;
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	private function _getEndActs($isPassed){
		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array();
			);

		$data = new Data_Vote();
		$temp = $data->getEndActs($isPassed);

		if(!$temp){
			$ret = array('errno'=> 0, 'errmsg' => 'error');
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$ret['data'] = $temp;
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	private function _setPass($actID, $isPassed = 1){
		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			);

		$data = new Data_Vote();
		$temp = $data->setPass($actID, $isPassed);

		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	private function _addAct(){
		if(!isset($_GET['actName']) || !isset($_GET['actImg']) ||
			!isset($_GET['actContent']) || !isset($_GET]['startTime']) ||
			!isset($_GET['endTime']) || !isset($_GET['conditions'])
			){
			$ret = array('errno' => 1, 'errmsg' => '参数不全')；
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$startTime = $_GET['startTime'];
		$endTime = $_GET['endTime'];
		if($startTime >= $endTime){
			$ret = array('errno' => 1, 'errmsg' => '开始时间比结束时间迟')；
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$actImg = json_decode($_GET['actImg'], true);
		if($actImg['errno']){
			echo $_GET['actImg'];
			return false;
		}
		$actImg = "http://wechatyiban.xmu.edu.cn/static/" . $actImg['data'];
		
		$conditions = json_encode($_GET['conditions'], JSON_UNESCAPED_UNICODE)
		$actName = $_GET['actName'];
		$actContent = $_GET['actContent'];

		$data = new Data_Vote();
		$temp = $data->addAct($actName, $actImg, $actContent, $startTime, $endTime, $conditions);

		$ret = array('errno' => 0, 'errmsg' => 'ok');
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;

	}

	private function _addPro(){

		if(!isset($_GET['proName']) || !isset($_GET['proImg']) ||
			!isset($_GET['proContent']) || !isset($_GET]['actID'])
			){
			$ret = array('errno' => 1, 'errmsg' => '参数不全')；
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$proImg = json_decode($_GET['proImg'], true);
		if($proImg['errno']){
			echo $_GET['proImg'];
			return false;
		}
		$proImg = "http://wechatyiban.xmu.edu.cn/static/" . $actImg['data'];
		
		$conditions = json_encode($_GET['conditions'], JSON_UNESCAPED_UNICODE)
		$proName = $_GET['proName'];
		$proContent = $_GET['proContent'];

		$data = new Data_Vote();
		$temp = $data->addPro($actID, $proName, $proImg, $proContent);

		$ret = array('errno' => 0, 'errmsg' => 'ok');
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;

	}

	private function _getActInfo($actID){
		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array();
			);

		$data = new Data_Vote();
		$actInfo = $data->getActInfo($actID);
		if(!$actInfo){
			$ret = array('errno'=> 0, 'errmsg' => 'error');
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}
		$ret['data'] = $actInfo;
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	private function _getProInfo($proID){
		$ret = array(
			'errno' => 0,
			'errmsg' => 'OK',
			'data' => array();
			);

		$data = new Data_Vote();
		$temp = $data->getProInfo($proID);

		if(!$temp){
			$ret = array('errno'=> 0, 'errmsg' => 'error');
			echo json_encode($ret, JSON_UNESCAPED_UNICODE);
			return false;
		}

		$ret['data'] = $temp;
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	private function _deleteAct(){

	}

	private function _deletePro(){}
}
?>