<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			Db.php
*	description:	签到平台
*
*	@author Yuri
*	@license Apache v2 License
*
**/



/**
* 签到平台Data类
*/
class Data_Db extends Data_Base
{

	function __construct($resource = NULL)
	{
		parent::__construct($resource);
	}

    public static function getXmuNum(){
        if(isset($_SESSION['openid'])){
            $db = Vera_Database::getInstance();
            $xmuId = $db->select('User', 'xmuId', array('wechatOpenid' => $_SESSION['openid']));
            if(empty($xmuId))
                return '';
            return $xmuId[0]['xmuId'];
        }
        return '';
    }

    public function isPayForExtend(){
        $db = Vera_Database::getInstance();
        $num = $this->getStuNum();
        return $db->selectCount('checkin_Log', array('xmu_num' => $num, 'isPay' => -1));
    }

    public function setAllToPay(){
        $db = Vera_Database::getInstance();
        $num = $this->getStuNum();
        $money = $db->select('checkin_Log', 'sum(money) money', array('xmu_num' => $num, 'isPay' => -1));
        $list = $db->select('checkin_Log', 'id', array('xmu_num' => $num, 'isPay' => -1));
        foreach($list as $log)
            $db->update('checkin_Log', array('isPay' => 1), array('id' => $log['id']));
        return $money[0]['money'];
    }

    public function getCheckinLog($isPay = -1){
        $db = Vera_Database::getInstance();
        $num = $this->getStuNum();
        $money = $db->select('checkin_Log', 'sum(money) money', array('xmu_num' => $num, 'isPay' => $isPay));
        $arr = array(
            'count' => $db->selectCount('checkin_Log', array('xmu_num' => $num, 'isPay' => $isPay)),
            'money' => $money[0]['money']
        );
        return $arr;
    }

	/**
	 * 回答问题
	 * @param  int 		$question_ID 问题编号
	 * @param  string 	$answer      回答内容
	 * @param  string 	$comments    附加备注信息
	 * @return boolean               回答是否成功
	 */

	public function addCheckinLog($question_ID, $answer, $source)
	{
		$num = $this->getStuNum();

		if(!$db = Vera_Database::getInstance()) {
			return false;
		}
		$insert = array(
				"xmu_num" => $num,
				"question_ID" => $question_ID,
				"answer" => $answer,
				"source" => $source,
				"answer_time" => date('Y-m-d H:i:s'),
                "isPay" => -1
			);
		$result = $db->insert('checkin_Log',$insert);
        $money = $this->getPrisePay();
        $rows = array('money' => $money);
        $db->update('checkin_Log', $rows, array('id' => $result));

		if (!$result) {
			Vera_Log::addWarning('database insert failed');
			return false;
		}
		return true;
	}

    public function isPay(){

        $num = $this->getStuNum();
        
        $db = Vera_Database::getInstance();
        $result = $db->select('checkin_Log', '*', array('xmu_num' => $num), NULL,array('order by id desc', 'limit 0,1'));
        if(isset($result[0]) && $result[0]['isPay'] > 0){
            return -1;
        }

        return $this->getPrisePay();
    }
    
    /**
     * 获取某天的问题(默认为当天)
     * @param  string  $date 日期 Y-m-d
     * @return mix  获取成功时返回题目数 组
     *              array   ID              题目ID
     *                      content         题目内容
     *                      questionType    题目类型
     *                      answerType      选项类型
     *                      comments        题目备注
     *         NULL 获取失败时返回NULL
     *         bool 若已签到返回flase
     */
    public function getQuestion($date = NULL)
    {
        if ($date === NULL) {
            $date = date("Y-m-d");//默认获取当天的问题
        }

        if(!$db = Vera_Database::getInstance()) {
            return false;
        }
       // $result = $db->update('checkin_List',array('rightAnswer'=>'A','questionType'=>0));
        $result = $db->select('checkin_List', '*', array('time' => $date));
        if ($result) {
            return $result[0];
        }
        else {
            return false;
        }
    }

    public function getOptionCount($question_ID, $option){
        $db = Vera_Database::getInstance();
        $condition = "question_ID = '{$question_ID}' and answer like '%{$option}%'";
        $count = $db->selectCount('checkin_Log',$condition);
        return $count;
    }

    public function insertQuestion($time, $data)
    {
        $db = Vera_Database::getInstance();
        //$update = $data;
        //$data['time'] = $time;
        // $insert = $data;
        // //利用MySQL特性 ON DUPLICATE KEY UPDATE，当违反time的unique时，使用update
        // $db->insert('checkin_List',$insert,NULL,$update);
        // return $db->getLastSql();
        
        $conds = array('time' => $time);
        $ret = $db->select('checkin_List', '*', $conds);
        if($ret == null){
            $data['time'] = $time;
            $ret = $db->insert('checkin_List',$data);
        }else{
            $ret = $db->update('checkin_List',$data,$conds);
        }
        if(!$ret){
            return false;
        }
        return true;
    }

    /**
     * 获取某日的签到人数
     * @param  string $date 日期
     * @return int       签到人数
     */
    public function getCount($date)
    {
        $temp = $this->getQuestion($date);
        if (!$temp) {
            return 0;
        }
        $db = Vera_Database::getInstance();
        return $db->selectCount('checkin_Log',array('question_ID'=>$temp['id']));
    }

    /**
     * 检查是否已绑定
     * @return boolean 绑定情况
     */
    public function isLinked()
    {
        return $this->getStuNum();
    }

    /**
     * 根据xmu_num获取易班相关信息
     * @param string $xmu_num 
     * @return array yiban_uid,yiban_islinked,access_token,expire_time
     * @author nili <nl_1994@foxmail.com>
     */
    public static function getYibanInfoByXmuNum($xmu_num) 
    {
        $ret = array('yiban_islinked' => 0,
            'yiban_uid' => 0,
            'access_token' => '',
            'expire_time' => '');
        $db = Vera_Database::getInstance();
        $user = $db->select('User', array('yibanUid', 'islinkedYiban'), array('xmuId' => $xmu_num));
        $ret['yiban_islinked'] = $user[0]['islinkedYiban'];
        $ret['yiban_uid'] = $user[0]['yibanUid'];
        
        if ($user[0]['islinkedYiban'])
        {
            $yiban = $db->select('Yiban', '*', array('uid' => $user[0]['yibanUid']));
            $ret['access_token'] = $yiban[0]['accessToken'];
            $ret['expire_time'] = $yiban[0]['expireTime'];
        }

        return $ret;
    }

	/**
	 * 今日是否已签到
	 * @param  int  $num 	学号
	 * @return boolean      是否已签到
	 */
	public function isChecked($num = NULL)
	{
        $num = $num === NULL ? $this->getStuNum() : $num;
		if (!$num) {
			return false;
		}

		if(!$db = Vera_Database::getInstance()) {
			return false;
		}

		$result = $db->select('checkin_Log', '*', array('xmu_num' => $num), NULL,array('order by id desc', 'limit 0,1'));
        if($result){
			return $this->_isToday($result[0]['answer_time']);//比较是否为今天
		}

		return false;
	}

	/**
	 * 检查时间是否为今天
	 * @param  string  $time 时间DATETIME格式
	 * @return boolean       是否为今天
	 */
	private function _isToday($time)
	{
		if( date("Y-m-d", strtotime($time)) == date("Y-m-d") )
			return true;
		else
			return false;
	}

    public function getPrisePay(){
        $num = $this->getStuNum();
        if(!$db = Vera_Database::getInstance()) {
            return false;
        }

        //当日签到排名
        $sql = "select sum1.id - sum2.id as result from";
        $sql.= "(select id from checkin_Log where xmu_num = '{$num}' order by id desc limit 0,1) sum1,";
        $sql.= "(select id from checkin_Log where answer_time < '". date("Y-m-d 0:0:0") ."' order by id desc limit 0,1) sum2";
        $result = $db->query($sql);

        if($result)
            $ret['order'] = $result[0]['result'];
        else
            $ret['order'] = -1;

        //可领取网薪数量
        if($ret['order'] > 10){
            $ret['pay'] = 5;
        }else{
            switch($ret['order']){
                    case 1:
                        $ret['pay'] = 50;
                        break;
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                        $ret['pay'] = 20;
                        break;
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                    case 10:
                        $ret['pay'] = 10;
                        break;
            }
        }

        return $ret['pay'];

    }


	/**
	 * 获取用户的签到信息
	 * @param  string $num 学工号
     * @param  string $question_id 问题ID
	 * @return Array      返回信息数组
	 *         	    order           今日签到排名
	 *         		monthCount      本月签到天数
	 */
	public function userCheckinInfo()
	{
		$num = $this->getStuNum();
		if(!$db = Vera_Database::getInstance()) {
        	return false;
        }

		//当日签到排名
		$sql = "select sum1.id - sum2.id as result from";
		$sql.= "(select id from checkin_Log where xmu_num = '{$num}' order by id desc limit 0,1) sum1,";
		$sql.= "(select id from checkin_Log where answer_time < '". date("Y-m-d 0:0:0") ."' order by id desc limit 0,1) sum2";
        $result = $db->query($sql);

		if($result)
			$ret['order'] = $result[0]['result'];
		else
			$ret['order'] = -1;

        //可领取网薪数量
        switch($ret['order']){
                case 1:
                    $ret['pay'] = 50;
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                    $ret['pay'] = 20;
                    break;
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                    $ret['pay'] = 10;
                    break;
                default:
                    $ret['pay'] = 5;
                    break;
        }

		//本月已签到次数
        $result = $db->selectCount('checkin_Log', "xmu_num = '{$num}' and answer_time > '". date("Y-m-1 0:0:0") ."'");

        if($result)
			$ret['count'] = $result;
		else
			$ret['count'] = -1;

		return $ret;
	}

	/**
     * 月签到排名
     * @param  string  $mode this本月，last上月
     * @param  integer $num  取排名的个数
     * @return array       排名列表
     */
    public function monthRank($mode = 'this', $num = 10)
    {
        if(!$db = Vera_Database::getInstance()) {
        	return false;
        }

        $table = 'checkin_Log';
        $fields = 'xmu_num as num,count(*) as count';
        $append = "group by num order by count desc,sum(id) limit 0,{$num}";
        if ($mode == 'this') {
            $condition = "answer_time >= '". date("Y-m-1 0:0:0") ."'";

            $result = $db->select($table, $fields, $condition, NULL, $append);
        }
        else if ($mode == 'last') {
            $temp = $this->_getPurMonth();
            $lastMonthFistday = $temp[0];
            $condition = "answer_time < '". date("Y-m-1 0:0:0") ."' and answer_time >= '". $lastMonthFistday ." 0:0:0'";

            $result = $db->select($table, $fields, $condition, NULL, $append);
        }
        else {
            return false;
        }

        if(!$result)
        	return false;

        return $result;
    }

    //获取上个月的第一天和最后一天
    private function _getPurMonth()
    {
    	$time=time();
    	$firstday=date('Y-m-01',strtotime(date('Y',$time).'-'.(date('m',$time)-1).'-01'));
    	$lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));

    	return array($firstday,$lastday);
    }

    /**
     * 获取正确答案
     * @param   int $question_ID 问题编号
     * @return  array 正确答案的数组
     * @author   <linjun>
     */
    public function getRightAnswer($question_ID){
        $db = Vera_Database::getInstance();
        if(!$db)
            return false;

        $result = $db->select('checkin_List', 'rightAnswer', array('id' => $question_ID));
        $result = explode('|', $result[0]['rightAnswer']);
        return $result;
    }

    /**
     * 获取备注，即问题解释
     * @param   int  $question_ID 问题编号
     * @return  string 
     * @author   <linjun>
     */
    public function getRemark($question_ID){
        $db = Vera_Database::getInstance();
        if(!$db)
            return false;
        $result = $db->select('checkin_List', 'remark', array('id' => $question_ID));
        return $result[0]['remark'];
    }

    /**
     * 获取问题类型
     * @param    $question_ID 问题ID
     * @return   int
     * @author  <linjun>
     */
    public function getQuestionType($question_ID){
        $db = Vera_Database::getInstance();
        if(!$db)
            return false;
        $result = $db->select('checkin_List','questionType', array('id' => $question_ID));
        return $result[0]['questionType'];
    }

/**
 * 检查答案是否正确
 * @Author   Lin
 * @Datetime 2015-07-06T18:44:52+0800
 * @param    int                   $question_ID 问题ID
 * @param    string                $answer      回答
 * @return   boolean               回答是否正确
 */
    public function isRight($question_ID, $answer){
        $db = Vera_Database::getInstance();
        if(!$db)
            return false;
        $rightAnswer = $this->getRightAnswer($question_ID);
        $answer = explode('|', $answer);

        if(count($rightAnswer) != count($answer)){
            return false;
        }

        for($i = 0; $i < count($rightAnswer); $i++){
            if($answer[$i] != $rightAnswer[$i]){
                return false;
            }
        }

        return true;
    }

/**
 * 获取回答比例
 * @Author   Lin
 * @Datetime 2015-07-06T18:52:03+0800
 * @param    int                   $question_ID 问题ID
 * @return   array[选项][数量]
 */
    public function getOptionRate($question_ID){
        $db = Vera_Database::getInstance();
        $options = $db->select('checkin_List','questionOption',array('id' => $question_ID));

        $options = explode('|',$options[0]['questionOption']);
        //$count = 0;
        for($i = 0; $i < count($options); $i++){
            $temp = explode('.', $options[$i]);
            $rate[$i]['key'] = $temp[0];
            $rate[$i]['value'] = $this->getOptionCount($question_ID, $temp[0]);
            //$count += $rate[$i]['value'];
        }

        // if($count == 0){
        //     return false;
        // }

        // for($i = 0; $i < count($options); $i++){
        //     $rate[$i]['value'] = $rate[$i]['value'] * 100 / $count;
        // }


        return $rate;
    }

    public function addPayLog($pay){
        
        $num = $this->getStuNum();
        
        $db = Vera_Database::getInstance();
        $result = $db->select('checkin_Log', '*', array('xmu_num' => $num), NULL,array('order by id desc', 'limit 0,1'));
        if($result[0]['isPay'] >= 0){
            return false;
        }

        $conds = array('ID' => $result[0]['ID']);
        $data = array('isPay' => 1);
        $ret = $db->update('checkin_Log', $data, $conds);

        if(!$ret){
            return false;
        }

        return true;
    }

}

?>
