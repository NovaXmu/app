<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Mall.php
*	description:	网薪换实物
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
*  网薪换实物信息操作
*/

class Data_Mall
{

    function __construct() {}

    /**
     * 设置上架或下架
     * @param  int      $id         商品id
     * @param  int      $state   上架/下架，1上架，-1下架
     * @author linjun test pass
     * 加上架时间
     */
    public static function setState($id, $state = -1)
    {
        $db = Vera_Database::getInstance();
        $row = array('state' => $state);
        $detail = self::getItemByID($id);
        $detail = $detail[0];
        if ($state == 1)
        {
            $row['onShelfTime'] = date("Y-m-d H:i:s");
            $row['startTime'] = empty($detail['startTime']) ? date("Y-m-d H:i:s") : $detail['startTime'];
        }
        $conds = array('id' => $id);
        $result = $db->update('mall_ItemsDetail', $row, $conds, NULL, NULL);
    }
   
   /**
     * 通过商品id从数据库里获取商品详情
     * @param  int      $id         商品id
     * @return array
     * @author Nili
     * 
     */
   public static function getItemByID($id){
        $db = Vera_Database::getInstance();
        $conds = array('id' => $id);
        return $db->select('mall_ItemsDetail', '*', $conds);
   }

   /**
     * 获取某种（竞价/兑换）商品列表
     * 
     * @param  int      $state        -1为下架商品，1为上架商品
     * @return  array      列表
     * @author linjun test pass
     */
    public static function getList($state)
    {
        $db = Vera_Database::getInstance();
        $conds = array('state' => $state);
        $append = 'order by id desc';
        $result = $db->select('mall_ItemsDetail', '*', $conds, null, $append);
        return $result;
    }

    /**
     * 删除商品
     * 
     * @param  int      $id      商品id
     * @return  int 影响行数    
     * @author Nili     done
     *没删图片，可能别的商品在用，但是图片就一直不删了吗T_T
     */
    public static function deleteItem($id)
    {
        $conds = array('id' => $id, 'state' => 0);
        $db = Vera_Database::getInstance();
        return $db->delete('mall_ItemsDetail', $conds);
    }

    /**
     * 获取某商品购买记录
     * 
     * @param  int      $itemID      商品id
     * @return  array 购买记录 
     * @author Nili     done
     *
     */
    public static function getPurchaseLog($itemID)
    {
        $conds = array('itemsID' => $itemID);
        $db = Vera_Database::getInstance();
        return $db->select('mall_Log', '*', $conds);
    }

     /**
     * 根据易班id获取学号
     * 
     * @param  int      $yibanID      易班id
     * @return  string 学号
     * @author Nili     done
     *
     */
    public static function getStuNum($yibanID)
    {
        $conds = array('uid' => $yibanID);
        $db = Vera_Database::getInstance();
        $ret = $db->select('User', 'xmuID xmu_num', $conds);
        if (empty($ret))
        {
            return '';
        }
        return $ret[0]['xmu_num'];
    }
   

    /**
     * 添加商品
     * @param   varchar     $name               商品名称
     * @param   int         $price              商品价格
     * @param   int         $amount             商品数量
     * @param   int         $type               商品类型，0为竞价商品，1为兑换商品，竞价商品要设定竞价结束时间
     * @param   string      $endTime            兑换商品兑换完的时间，或竞价结束时间，若添加兑换商品则此项为NULL，添加竞价商品必须设定竞价结束时间,字符串格式：2015-03-22 22:47:00 
     * @param   array       $limitConds         限制条件 
     * @param   string      $pic                商品图片URL
     * @param   string      $description        商品描述
     * @param   string      $startTime          开始时间
     * @return  int                             影响行数
     * @author linjun  test pass
     */
    public static function addItem($name, $price, $amount, $type, $limitConds, $pic, $description, $endTime=NULL, $startTime = null)
    {
         $db = Vera_Database::getInstance();
         $insert = array(
            'name' => $name,
            'price' => $price,
            'amount' => $amount,
            'type' => $type,
            'limitConds' => $limitConds,
            'state' => 0,
            'pic' => $pic,
            'description' => $description,
            'endTime' => $endTime,
             'startTime' => $startTime
            );
         return $db->insert('mall_ItemsDetail',$insert);
    }

    /**
     * 更新商品
     * @param   int     $id               商品id
     * @param   int         $price              商品价格
     * @param   int         $amount             商品数量
     * @param   int         $type               商品类型，0为竞价商品，1为兑换商品，竞价商品要设定竞价结束时间
     * @param   string      $endTime            兑换商品兑换完的时间，或竞价结束时间，若添加兑换商品则此项为NULL，添加竞价商品必须设定竞价结束时间,字符串格式：2015-03-22 22:47:00 
     * @param   array       $limitConds         限制条件 
     * @param   string      $description        商品描述
     * @param   string      $startTime          开始时间
     * @return  int                             影响行数
     * @author Nili
     */
    public static function updateItem($id, $price, $amount, $type, $limitConds, $description, $endTime=NULL, $startTime=null)
    {
         $db = Vera_Database::getInstance();
         $update = array(
            'price' => $price,
            'amount' => $amount,
            'type' => $type,
            'limitConds' => $limitConds,
            'state' => 0,
            'description' => $description,
            'endTime' => $endTime,
             'startTime' => $startTime
            );
         return $db->update('mall_ItemsDetail',$update, array('id' => $id));
    }

    /**
    *兑换密匙的审核
    *返回学号,姓名（暂时用$userid代替），兑换商品名称
    *@rewrite nili
    */
   /**
    * 兑换token校验
    * @param  string $token 
    * @param array $data 引用类型，为了方便放数据
    * @return string  ''为正常
    * @author longyang 
    * @rewrite by nili
    */
    public static function checkTokenIsused($token, &$data){
        $db = Vera_Database::getInstance();
        $conds = array('token' => $token, 'isUsed' => 1);
        $log = $db->select('mall_Log', '*', $conds);
        if (empty($log))
        {
            return '无购买记录或已被兑换';
        }
        $log = $log[0];

        $item = $db->select('mall_ItemsDetail', '*', array('id' => $log['itemsID']));
        $item = $item[0];
        $tokenEffectiveDay = self::getCache('itemEffectiveDay');
        $tokenEffectiveDay = empty($tokenEffectiveDay) ? 7 : $tokenEffectiveDay;
        if ($item['type'])//兑换商品
        {
            $tokenExpires = strtotime($log['time']) + $tokenEffectiveDay * 3600 * 24;
        }
        else
        {
            $tokenExpires = strtotime($item['endTime']) + $tokenEffectiveDay * 3600 * 24;
        }
        if ($tokenExpires < time())
        {
            return '该token已过期';
        }

        $user = $db->select('User', '*', array('yibanUid' => $log['userID']));
        if (empty($user))
        {
            return '查不到该同学信息';
        }
        $user = $user[0];
        $data = array(
            'name' => $log['userID'],
            'itemsName' => $item['name'],
            'xmu_num' => $user['xmuId'],
            'logID' => $log['id']
            );
        return '';
    }

    
    /**
    *将mall_log中的'isused'置为0(用没了)
    */
    public static function setTokenUsed($logID){
        $db = Vera_Database::getInstance();
        $conds = array('id' => $logID);
        $row = array('isUsed' => 0);
        $result = $db->update('mall_Log',$row,$conds,NULL,NULL);
        if($result){
               return true;
          }
          else{
               return false;
          }
    }


    /*
     * setCache,管理员设置相关参数，比如商品有效领取时间，比如购买者可同时购买多少种已上架商品
     * @param string $key,cache 里的的key形式为 mall_$key,前缀'mall_' 自动补上
     * @param int $value
     * @author nili
     */
    public static function setCache($key, $value)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'mall_' . $key;
        $cache->set($key, $value, time() + 3600 * 24 * 30);
    }

    /*
     * getCache,与setCache相对应
     * @author nili
     */
    public static function getCache($key)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'mall_' . $key;
        return $cache->get($key);
    }
}

?>