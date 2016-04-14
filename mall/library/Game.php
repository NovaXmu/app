<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Game.php
*	description:	网薪商城相关游戏规则
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Library_Game
{

	public static function getAwardByScore($score)
	{
		if ($score < 1)
		{
			return 0;
		}
		switch ($score) {
			case $score > 20000:
				$award = 150;	
				break;
			case $score > 15000:
				$award = 120;	
				break;
			case $score > 12000:
				$award = 80;	
				break;
			case $score > 10000:
				$award = 50;	
				break;
			case $score > 3000:
				$award = 30;	
				break;
			case $score > 2000:
				$award = 20;	
				break;
			case $score > 1000:
				$award = 10;	
				break;
			default:
				$award = 0;
				break;
		}
		return $award;
	}

	/**
	 * 博饼，生成随机数及对应网薪值
	 * @return array 		包括6个随机数（1-6之间），和一个对应取得的网薪值
	 * @author linjun  test pass
	 */
	public static function bobing(){
		$numbers = "";
		$money = 0;
		$arr = array('1' => 0,
			'2' => 0,
			'3' => 0,
			'4' => 0,
			'5' => 0,
			'6' => 0);
		for($i = 1; $i < 7; $i++){
			$die = mt_rand(1,6);
			$arr[$die]++;
			$numbers .= $die;
		}
		switch($arr['4']){
			case 6: //六勃红
				$money = 100;
				break;
			case 5: //五红
				$money = 70;
				break;
			case 4:
				if($arr['1'] == 2){//状元插金花
					$money = 120;
				}else{
					$money = 50;
				}
				break;
			case 3://三红
				$money = 27;
				break;
			case 2:
				if($arr['1']==4 || $arr['2']==4 || $arr['3']==4 ||
					$arr['5']==4 || $arr['6']==4)//四进带二举
					$money = 25;
				else $money = 10;//二举
				break;
			case 1:
				if($arr['1']==1 && $arr['2']==1 && $arr['3']==1 &&
					$arr['5']==1 && $arr['6']==1)//对堂
					$money = 30;
				else if($arr['1']==5 || $arr['2']==5 || $arr['3']==5 ||
					$arr['5']==5 || $arr['6']==5)//五子带一秀
					$money = 65;
				else if($arr['1']==4 || $arr['2']==4 || $arr['3']==4 ||
					$arr['5']==4 || $arr['6']==4)//四进带一秀
					$money = 20;
				else $money = 5;//一秀
				break;
			case 0:
				if($arr['6'] == 6)//六勃黑
					$money = 80;
				else if($arr['3'] == 5)//五子登科
					$money = 60;
				else if($arr['2'] == 4)//四进
					$money = 15;
				else if($arr['1'] == 6)//遍地锦
					$money = 90;
				break;
		}
		$result = array('dice' => $numbers, 'money' => $money);
		return $result;
	}

	/**
	 *根据网薪值对应各种博饼的提示
	 * @param  int             $award       网薪值
	 * @param  int             $type       type为1时表示附加信息也要返回
	 * @return string
	 * @author Nili      done
	 */
	public static function getBobingLeval($award, $type=0)
	{
		$add = '赚大了！';
		switch ($award) {
			case 120:
				$str1 = '状元插金花';
				break;
			case 100:
				$str1 = '六勃红';
				break;
			case 90:
				$str1 = '遍地锦';
				break;
			case 80:
				$str1 = '六勃黑';
				break;
			case 70:
				$str1 = '五红';
				break;
			case 65:
				$str1 = '五子带一秀';
				break;
			case 60:
				$str1 = '五子登科';
				break;
			case 50:
				$str1 = '状元';
				break;
			case 30:
				$str1 = '对堂';
				break;
			case 27:
				$str1 = '三红';
				break;
			case 25:
				$str1 = '四进带二举';
				break;
			case 20:
				$str1 = '四进带一秀';
				$add = '加油哟';
				break;
			case 15:
				$str1 = '四进';
				$add = '再博博找找灵感';
				break;
			case 10:
				$str1 = '举人';
				$add = '换个姿势再博一次';
				break;
			case 5:
				$str1 = '秀才';
				$add = '前途茫茫';
				break;
			default:
				$str1 = '没中奖';
				$add = '赶紧加油';
				break;
		}
		if ($type)
			return $str1 . ' ' . $add;
		return $str1;
	}


}

?>