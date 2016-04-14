<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Luck.php
*   description:      抽奖接口(临时)
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

//@temp: 抽奖
class Action_Api_Luck extends Action_Base{
	function __construct(){}

	public function run(){
		return true;//放弃服务端抽奖逻辑，改用前端逻辑


		switch($_GET['m']){
			case 'draw':
				return $this->_draw();
				break;

			default:
				break;
		}
	}

	private static function _draw(){
		$ret = array('errno' => 0, 'errmsg' => 'OK', 'data' => array());
		try{
			$data = self::_getDraw();//获取中奖者信息
			$ret['data'] = $data;
		}catch(Exception $e){
			$ret = array(
				'errno' => $e->getCode(),
				'errmsg' => $e->getMessage()
				);
		}

		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		return true;
	}

	/**
	* @return array $ret 所有中奖人信息
	*/
	private static function _getDraw(){
		$id = 1;
		$fileContents = '';
		while($id<2){
			$fileContents .= file_get_contents(SERVER_ROOT.'data/temp/'.$id.'.data');
			$id++;
		}

		$list = array();//参与人员列表
		$list = explode("\n",$fileContents);

		$count = count($list);//统计参与数量

		$luckNum = 6;//总共中奖的人数

		$ret = array();//中奖者信息

		for($i = 0; $i < $luckNum; $i++){
			$flag = 1;//标记位，1.被抽中的人尚未中奖。 0.被抽中的人已经中奖
			$rand = mt_rand(0,$count-1);
			$temp = $list[$rand];
			$temp = explode(' ',$temp);//分割每行的信息
			$arr = array(//#。。。还有信息
				'yiban_id' => $temp[0]
				);
			foreach($ret as $next){
				if($next['yiban_id'] == $arr['yiban_id']){//如果被抽中的人已经中奖则i-=1,标记位置为0
					$i--;
					$flag = 0;
					break;
				}
			}
			if($flag == 1)//被抽中的人尚未中奖，则将其信息加入到中奖者信息数组中
				$ret[$i] = $arr;
		}

		return $ret;
	}

}
?>
