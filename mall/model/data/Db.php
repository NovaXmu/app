<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Db.php
*	description:	网薪换实物Data层数据获取封装
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Data_Db 
{
	
	function __construct()
	{

	}

	/**
     * 获取商品列表
     * @param  string  	$order  排序方式，例：startTime（上架时间）即获取最新上架的商品列表
     * @param  int  	$type 	商品类型，0即竞价商品，1即兑换商品
     * @return array 		商品列表，建议几组就行
     * @author linjun   test pass
     */
	public static function getItemsList($order,$type){
          $db = Vera_Database::getInstance();
            $conds = array(
               'state' => 1,//state=1 上架 
               'type' => $type
               );
            $append = 'order by onShelfTime desc';//要desc
            $result = $db->select('mall_ItemsDetail', '*', $conds, NULL, $append);
            if($result){
               return $result;
            }
            else{
               return false;
            }
	}


	/**
     * 获取某商品详细信息,从数据库里
     * @param  int  	$id 	商品id
     * @return array 	 商品详细信息
     * @author linjun   test pass
     */
	public static function getItemDetail($id){
          $db = Vera_Database::getInstance();
          $conds = array('id' => $id);
          $result = $db->select('mall_ItemsDetail', '*', $conds, NULL, NULL);
          if(!empty($result)){
               return $result[0];
          }
          else{
               return false;
          }
	}

     /**
     * 获取某人的所有log 
     * @param  string         $userID    用户的易班id
     * @return array          该用户所有log
     * 注意如果对同一商品有多条竞价log，选择出价最高的那条即可，其他不选
     * 注意如果对同一商品有多条兑换log，全选
     * @author linjun  test pass
     */
     public static function getPersonLog($userID){
          $db = Vera_Database::getInstance();
          $conds = array('userID' => $userID);
          $append = 'order by itemsID ASC';
          $result = $db -> select('mall_Log', '*', $conds, NULL, $append);
          $arr = array();
          if(empty($result))
               return $arr;
          $x = 0;
          foreach($result as $next){
               if (!$x)
               {
                    $arr[$x] = $next;
                    $x ++;
                    continue;
               }
               $condition = array('id' => $next['itemsID']);//added
               $itemsDetail = $db -> select('mall_ItemsDetail','*',$condition);//changed
               if($itemsDetail[0]['type'] == 1)
               {
                    $arr[$x] = $next; 
               }
               else{
                    $x --;
                    if($arr[$x]['itemsID'] == $next['itemsID']){
                         if($arr[$x]['price'] < $next['price'])
                         {
                              $arr[$x] = $next;
                         }
                    }
                    else{
                         $x ++;
                         $arr[$x] = $next;
                    }
               }
               $x ++;
          }
          return $arr;
     }


     /**
     * 从mall_Log表中set某条特定记录的token
     * @param  int      $id   某条记录的id
     * @param  string      $token 
     * @return int      影响行数
     * @author linjun  test pass
     */
     public static function updateToken($id, $token){
          $db = Vera_Database::getInstance();
          $conds = array('id' => $id);
          $row = array('token' => $token, 'isUsed' => 1);//加了isUsed为1
          $result = $db -> update('mall_Log', $row, $conds, NULL, NULL);
          if($result){
               return true;
          }
          else return false;
     }

	/**
     * 重置商品价格
     * @param  int  	$id 	商品id
     * @param  int  	$price 	重置的商品价格
     * @return true
     * @author linjun   test pass
     */
	public static function setPrice($id,$price){
          $db = Vera_Database::getInstance();
          $conds = array('id' => $id);
          $row = array('price' => $price);
          $result = $db->update('mall_ItemsDetail', $row, $conds, NULL, NULL);
          if($result){
               return true;
          }
          else{
               return false;
          }
	}

     /**
     * 在mall_ItemsDetail表里，设置兑换商品的结束时间为当前时刻
     * @param  int       $id  商品id
     * @return int 影响行数
     * @author linjun  test pass
     */
     public static function setEndTime($id){
          $db = Vera_Database::getInstance();
          $conds = array('id' => $id);
          $rows = array('endTime' => date('Y-m-d H:i:s'));
          $result = $db -> update('mall_ItemsDetail', $rows, $conds, NULL, NULL);
     }



     /**
     * 根据商品id检查log表中当前出价最高者有没有token值
     * @param  int       $id  商品id
     * @return 有token返回false，没有就返回该条记录
     * @author linjun  test pass
     */
     public static function checkAuctionToken($id){
          $db = Vera_Database::getInstance();
          $conds = array('itemsID' => $id);
          $arr = $db->select('mall_Log', '*', $conds, NULL, NULL);
          if(!$arr) return false;
          $max = $arr[0];
          foreach($arr as $detail){
               if($max['price'] < $detail['price'])
                    $max = $detail;
          }
          if($max['token'])
               return false;
          else
               return $max;
     }

     /**
     * 检查限制条件是否满足，第二个参数为正，则表示要大于limit，为负，则要小于limit
     * @param  string         $user 用户条件
     * @param  string         $limit    限制条件
     * @return bool           true 满足
     * @author Nili  done 
     */
     public static function checkLimits($user, $limit){
          if ($limit < 0)
          {
               return $user < abs($limit) ? true:false; 
          }
          if ($limit > 0)
          {
               $tem = $user - $limit;
               return $tem > 0 ? true:false;
          }
          return true;
     }


     /**
     * 生成凭证号
     * @return  int       $token        12位随机数凭证号
     * @author linjun  test pass
     */
     public static function createToken(){
          $token = "";
          for($i = 0; $i < 12; $i++){
               $token .= mt_rand(0,9);
          }
          return $token;
     }

	/**
     * 兑换商品，在数据库写入一条兑换记录
     * @param  string  	$userID 	    用户易班id
     * @param  int  	$id 	         商品id
     * @param  int  	$price 	    商品价格
     * @param  int       $token        凭证号
     * @return int       影响行数
     * @author linjun   test pass
     */
	public static function exchange($userID, $id, $price, $token = NULL){
          $db = Vera_Database::getInstance();
          $insert = array(
               'itemsID' => $id,
               'userID' => $userID,
               'price' => $price,
               'time' => date('Y-m-d H:i:s'),
               'token' => $token,
               'isUsed' => 1 //加了isUsed为1
               );
          return $db->insert('mall_Log',$insert);
	}

	/**
     * 竞价，在数据库写入一条竞价记录
     * @param  string  	$userID 	用户易班id
     * @param  int  	$id 	     商品id
     * @param  int  	$price 	商品价格
     * @return int 				影响行数
     * @author linjun    done
     */
	public static function auction($userID, $id, $price){
          $db = Vera_Database::getInstance();
          $insert = array(
               'userId' => $userID,
               'itemsID' => $id,
               'price' => $price,
               'time' => date("Y-m-d H:i:s")
               );
          return $db->insert('mall_Log',$insert);
	}

     /**
     * 获取某商品剩余数量，log表
     * @param  int      $id      商品id
     * @param  int      $amount       初始数量
     * @return int      $result   剩余数量
     * test pass
     */
     public static function getItemRemainAmount($id, $amount){
          $db = Vera_Database::getInstance();
          $conds = array('itemsID' => $id);
          $result = $db->select('mall_Log', '*', $conds, NULL, NULL);
          $count = 0;
          foreach($result as $next)
               if($next['token'])
                   $count++;
          return $amount-$count;
     }

     /*
     * 博饼，在数据库里写入一条记录
     * @param $yb_uid 
     * @param $bobing 数组包括 dies 和 money
     * @param  $awardTimes  博饼奖励倍数，于2015-10-30新增 by nili
     * @return 
     * @author linjun  test pass
     */
     public static function addBobingLog($yb_uid, $Bobing, $awardTimes = 1){
          $db = Vera_Database::getInstance();
          $rows = array('yb_uid' => $yb_uid,
               'randNum' => $Bobing['dice'],
               'award' => $Bobing['money'],
               'fromAct' => 'bobing',
               'awardTimes' => $awardTimes,
               'time' => date("Y-m-d H:i:s"));
          $result = $db -> insert('mall_Bobing', $rows);
          return $result;
     }

     /**
     * 获取当前用户今日已用博饼次数
     * @param  int      $userID       用户id
     * @return int          已用次数
     * @author linjun
     */
     public static function getRemainTimes($userID){
          $db = Vera_Database::getInstance();
          $conds = array('yb_uid' => $userID, 'fromAct' => 'bobing');
          $result = $db -> select('mall_Bobing', '*', $conds);
          $count = 0;
          $now = date("Y-m-d");
          foreach($result as $next){
               $arr = explode(' ',$next['time']);
               if($arr[0] == $now)
                    $count++;
          }
          return $count;
     }


     /**
     *在User表里插入用户的yiban_uid,根据学号插入
     * @param  array          $userInfo       
     * @return int            影响行数
     * @author linjun   test pass
     */
     public static function setYibanUid($userInfo){
          $db = Vera_Database::getInstance();
          $conds = array('xmuId' => $userInfo['yb_studentid']);
          $rows = array('yibanUid' => $userInfo['yb_userid']);
          $result = $db -> update('User', $rows, $conds, NULL, NULL);
     }

     /**
     *在vera_Yiban表里插入用户信息
     * @param  array          $userInfo       用户信息，各种
     * @return int            影响行数
     * @author linjun         done
     */
     public static function insertUserInfo($userInfo){
          $db = Vera_Database::getInstance();
          $data = array(
               'accessToken' => $userInfo['access_token'],
               'expireTime' => $userInfo['token_expires']
               );
          $update = $data;
          $data['uid'] = $userInfo['yb_userid'];
          $insert = $data;
          $db->update('User', array('yibanUid' => $userInfo['yb_userid']), array('xmuId' => $userInfo['yb_studentid']));
          
          //利用MySQL特性 ON DUPLICATE KEY UPDATE，当违反uid的unique时，使用update
          return $db->insert('Yiban', $insert, NULL, $update);
     }


     /**
     *在vera_Yiban取出access_token
     * @param  string          $uid       易班uid
     * @return string
     * @author Nili         done
     */
     public static function getAccessToken($uid){
          $db = Vera_Database::getInstance();
          $access_token = $db->select('Yiban', 'accessToken as access_token', array('uid' => $uid));
          if (isset($access_token) && !empty($access_token)) {
               return $access_token[0]['access_token'];
          }
          return '';
     }

     /**
     *在mall_Bobing表里取出最新的博饼记录
     * @param  string          $uid       易班uid
     * @param  int             $num       所取记录条数
     * @param  string          $fromAct       游戏
     * @return array
     */
     public static function getYibanAwardLog($uid, $num, $fromAct)
     {
          $db = Vera_Database::getInstance();
          $append = "order by time desc limit 0, {$num}";
          $conds = array('yb_uid' => $uid, 'fromAct' => $fromAct);
          $result = $db->select('mall_Bobing', 'id, yb_uid, randNum, fromAct, award * awardTimes as award, time', $conds, NULL, $append);

          if(empty($result)){
               return false;
          }
          return $result;
     }

     /**
     *在mall_Bobing表里取出今日最佳
     * @param  int             $num       所取记录条数
     * @param  string          $fromAct       游戏
     * @return array
     * @author Nili      done
     */
     public static function getEachBobingLog($num, $fromAct)
     {
          $db = Vera_Database::getInstance();
          $append = "order by award desc, id desc limit 0, {$num}";
          $time = date("Y-m-d").' 00:00:00';
          $conds = "`fromAct`='{$fromAct}' AND `time`>'{$time}'";
          $result = $db->select('mall_Bobing', '*', $conds, NULL, $append);
          if(empty($result)){
               return array();
          }
          return $result;
     }

     /**
     *计算当前用户在博饼活动中获得的总网薪值
     * @param  string          $fromAct       游戏
     * @param  string          $uid       易班uid
     * @return int
     */
     public static function getTotalAward($fromAct, $uid)
     {
          $db = Vera_Database::getInstance();
          $conds = array('yb_uid' => $uid, 'fromAct' => $fromAct);
          $result = $db -> select('mall_Bobing', '*', $conds);
          if(!$result){
               return 0;
          }
          $award = 0;
          foreach($result as $next){
               $award += $next['award'];
          }
          return $award;
     }

     /**
     * 获得某商品当前已兑换数量,借个位置T_Tcms里changeApp到mall这儿来了，这只是在cms里用到的
     * 
     * @param  int      $id         商品id
     * @return  int     已兑换数量
     * @author Nili
     */
    public static function getUsedAmount($id)
    {
        $db = Vera_Database::getInstance();
        $conds = '`itemsID`=' . $id . ' AND `token`is not NULL AND `isUsed`=0';
        $result = $db->select('mall_Log', '*', $conds, null, null);
        return count($result);
    }

    /**
     * 获取最高成绩，game2048用
     * 
     * @param  int      $yb_userid         
     * @return  int     最高成绩
     * @author Nili
     */
    public static function getTodayHighestScore($yb_userid)
    {
        $db = Vera_Database::getInstance();
        $date = date("Y-m-d");
        $conds = "`yb_uid`={$yb_userid} AND `fromAct`='game2048' AND `time`>'{$date}' order by randNum desc limit 1";
        $result = $db->select('mall_Bobing', 'randNum', $conds, null, null);
        return empty($result) ? 0 : $result[0]['randNum'];//随机数这一列用在2048是得分
    }

    /**
     * 获取上条游戏记录的id
     * 
     * @param  int      $yb_userid         
     * @return  int     id
     * @author Nili
     */
    public static function getLastPlayID($yb_userid)
    {
        $db = Vera_Database::getInstance();
        $conds = "`yb_uid`={$yb_userid} AND `fromAct`='game2048' order by id desc limit 1";
        $result = $db->select('mall_Bobing', 'id', $conds, null, null);
        return empty($result) ? 0 : $result[0]['id'];
    }

    /**
     * 插入一条游戏记录，game2048的
     * 
     * @param  int      $yb_userid     
     * @param int $awardTimes 奖励倍数，2015-10-31新增    
     * @return  int     id
     * @author Nili
     */
    public static function saveScore($yb_userid  , $score, $award, $awardTimes = 1)
    {
          $db = Vera_Database::getInstance();
          $rows = array('yb_uid' => $yb_userid,
               'randNum' => $score,
               'award' => $award,
               'fromAct' => 'game2048',
               'awardTimes' => $awardTimes,
               'time' => date("Y-m-d H:i:s"));
          $result = $db -> insert('mall_Bobing', $rows);
          return $result;
    }

     /**
      *在mall_Bobing表里取出game2048今日排行榜数据
      * @param  int             $num       所取记录条数
      * @param  string          $fromAct       游戏
      * @return array
      * @author Nili      done
      */

    public static function getScoreRank($num, $fromAct)
    {
          $db = Vera_Database::getInstance();
          $append = "order by randNum DESC,time DESC";
          $time = date("Y-m-d");
          $conds = "`fromAct`='{$fromAct}' AND `time`>'{$time}'";
          $result = $db->select('mall_Bobing', '*', $conds, NULL, $append);
          if(empty($result)){
               return array();
          }
          $ret = array();
          foreach ($result as $key => $row) {
            if (isset($ret[$row['yb_uid']])){
              continue;
            }
            $tmp = $db->select('mall_Bobing', 'sum(award * awardTimes) as award', "yb_uid={$row['yb_uid']} AND " . $conds );
            $ret[$row['yb_uid']] = $row;
            $ret[$row['yb_uid']]['award'] = $tmp[0]['award'];
            if (count($ret) >= $num){
              break;
            }
          } 
          return $ret;
    }

    function getReAwardData()
    {
        $db = Vera_Database::getInstance();
        return $db->select("mall_ReAward", '*');
    }

    function updateWxLog($info, $where)
    {
        $db = Vera_Database::getInstance();
        $db->update('mall_Bobing', $info, $where);
    }

    function updateReWardLog($info, $where)
    {
        $db = Vera_Database::getInstance();
        $db->update('mall_ReAward', $info, $where);
    }
 
}
?>