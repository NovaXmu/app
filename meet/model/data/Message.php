<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Message.php
*   description:      Data for Message.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Data_Message{
    function __construct(){}

/**
 * 添加消息记录   
 *
 * @param int $from    
 * @param int $to      
 * @param int $type    1.用户 2.活动 3.部落 4.PK
 * @param string $content 消息内容
 */
    public static function addMessage($from, $to, $type, $content){
        $data = array(
            'from_user_ybid' => $from,
            'to_obj_id' => $to,
            'obj_type' => $type,
            'content' => $content,
            'time' => date('Y-m-d H:i:s'),
            'isRead' => -1
            );

        $db = Vera_Database::getInstance();
        $ret= $db->insert('meet_Message', $data);
        if(!$ret){
            return false;
        }
        return true;
    }

    public static function getMessage($type, $obj_id, $index){
        if($type == 1){//获取悄悄话
            $user_ybid = $_SESSION['yb_user_info']['yb_userid'];
            $conds = "from_user_ybid IN ($user_ybid,$obj_id) AND to_obj_id IN ($user_ybid,$obj_id) AND obj_type = $type";
        }else{//获取其他类型的消息
            $conds = array(
                'to_obj_id' => $obj_id,
                'obj_type' => $type
                );
        }

        $appends = 'order by time desc limit '. ($index * 10) .',10';

        $db = Vera_Database::getInstance();
        $list = $db->select('meet_Message', '*', $conds, NULL, $appends);

        if(is_bool($list)){
            //记Log
            return array();
        }

        return $list;
    }


    public static function getMessageMenu($to_obj_id, $type){
        $db = Vera_Database::getInstance();
        if($type == 1){
            //1.获取悄悄话列表 
            //PS 没有按照时间来排序 因为group by 和 order by 同时使用达不到需求
            $conds = array('obj_type ' => 1, 'to_obj_id' => $to_obj_id, 'isRead' => -1);
            $appends = 'group by from_user_ybid';
            $list = $db->select('meet_Message', 'from_user_ybid user_ybid, count(id) count', $conds, NULL, $appends);
            if(is_bool($list)){
                //记Log
                $list = array();
            }
            //获取没有未读消息，但曾经给你发过消息的人
            $conds = "obj_type = 1 AND to_obj_id = $to_obj_id";
            if(count($list) != 0){
                $arr = array();
                foreach($list as $key => $value)
                    $arr[] = $value['user_ybid'];
                $conds .= ' AND from_user_ybid NOT IN (' . implode(',', $arr) . ')';
            }
            $temp = $db->select('meet_Message', 'from_user_ybid user_ybid', $conds, NULL, $appends);
            if(is_bool($temp)){
                //记Log
                //var_dump($db->getLastSql());
                $temp = array();
            }
            foreach($temp as $key => $value)
                $temp[$key]['count'] = 0;

            //合并联系人
            $list = array_merge($list, $temp);
        }else{//获取PK未读消息计数
            $conds = array('obj_type ' => 4, 'to_obj_id' => $to_obj_id, 'isRead' => -1);
            $list = $db->select('meet_Message', 'count(id) count', $conds);
        }
        if(is_bool($list)){
            //记Log
            return array();
        }

        return $list;
    }

    public static function setMessageReaded($type, $to_obj_id, $from_user_ybid = NULL){
        $db = Vera_Database::getInstance();
        $conds = array('obj_type' => $type, 'to_obj_id' => $to_obj_id, 'isRead' => -1);
        if($from_user_ybid){//针对悄悄话，如果没有则是PK
            $conds['from_user_ybid'] = $from_user_ybid;
        }
        $row = array('isRead' => 1);
        $ret = $db->update('meet_Message', $row, $conds);

        if(!$ret){
            //记Log
            //var_dump($db->getLastSql());
            return false;
        }

        return true;
    }

}
?>