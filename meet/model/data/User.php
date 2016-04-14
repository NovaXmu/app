<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             User.php
*   description:      Data for User.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Data_User{
    function __construct(){}

    public static function addUser($ybid, $nickname, $sex){
        $data = array(
            'ybid' => $ybid,
            );

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_User', '*', $data);
        if(isset($ret[0]['id'])){
            return true;
        }

        $rows = array('yibanNickname' => $nickname, 'sex' => $sex);
        $db->update('User', $rows, array('yibanUid' => $ybid));

        $data['nickname'] = $nickname;
        $data['interpersonal'] = 0;
        $data['charm'] = 0;
        $data['figure'] = 0;
        $data['time'] = date('Y-m-d H:i:s');
        $data['sex'] = $sex;
        $ret = $db->insert('meet_User', $data);
        if(!$ret){
            //var_dump($db->getLastsql());
            return false;
        }

        return true;
    }

    public static function getUserInfo($user_ybid){

        $conds = array('ybid' => $user_ybid);

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_User', '*', $conds);
        if(!$ret){
            //记Log
            return false;
        }

        return $ret[0];
    }

    public function getUserLabelIdList($user_ybid){
        $db = Vera_Database::getInstance();
        $conds = array('user_ybid' => $user_ybid);
        $appends = 'order by label_id';
        $ret = $db->select('meet_LabelLog', 'label_id', $conds, NULL, $appends);
        if(is_bool($ret)){
            return false;
        }

        $list = array();
        foreach($ret as $key => $value){
            $list[] = $value['label_id'];
        }

        return $list;
    }

    public function similarRate($user1, $user2){

        $user1 = $this->getUserLabelIdList($user1);
        $user2 = $this->getUserLabelIdList($user2);

        $list = array_merge($user1, $user2);
        $sum = count($list);
        if($sum == 0){
            return 0;
        }
        $count = count(array_unique($list));
        $rate = (($sum - $count) * 2) / $sum * 100;

        return intval($rate);
    }

    public function getLabelInfoByArr($arr = NULL, $type = 0){
        $db = Vera_Database::getInstance();
        $conds = '(1 = 1)';
        if(is_array($arr)){
            $conds .= 'AND (id IN (' . implode(',', $arr) . '))';
        }
        if($type != 0){
            $conds .= " AND type = $type";
        }
        $appends = 'order by type';
        $ret = $db->select('meet_Label', '*', $conds, NULL, $appends);
        if(is_bool($ret)){
            return false;
        }

        return $ret;
    }

    public static function PK($user1_ybid, $user2_ybid){

        $ret['me'] = self::getUserInfo($user1_ybid);
        $ret['he'] = self::getUserInfo($user2_ybid);

        if(is_bool( $ret['me']) || is_bool($ret['he'])){
            //记Log
            return 0;
        }

        $ret['point1'] = rand(0, $ret['me']['interpersonal']) + rand(0, $ret['me']['charm']) + rand(0, $ret['me']['figure']);
        $ret['point2'] = rand(0, $ret['he']['interpersonal']) + rand(0, $ret['he']['charm']) + rand(0, $ret['he']['figure']);

        $ret['distance'] = $ret['point1'] - $ret['point2'];

        if($ret['distance'] == 0){
            $ret['result'] = 1;//平局
            $ret['message'] = '平局~ 下次再战';
        }else if($ret['distance'] > 0){
            $ret['result'] = 2;//user1获胜
            $ret['message'] = 'haha~ 小样儿 我赢啦~';
        }else{
            $ret['result'] = 3;//user2获胜
            $ret['message'] = '被欺负了 T~T ';
        }

        //记录PK
        $data = array(
            'from_user_ybid' => $user1_ybid,
            'to_obj_id' => $user2_ybid,
            'obj_type' => 4,
            'content' => $ret['result'],
            'time' => date('Y-m-d H:i:s'),
            'isRead' => -1
            );
        $db = Vera_Database::getInstance();
        $insert = $db->insert('meet_Message', $data);
        if(!$insert){
            //记Log
        }

        return $ret;
    }

    public function circle($user_ybid){
        $db = Vera_Database::getInstance();
        $self = $db->select('meet_User', 'id', array('ybid' => $user_ybid));
        $ret = $db->select('meet_User', '*', "ybid != $user_ybid", NULL, 'order by rand() limit 5');
        if(is_bool($ret)){
            $ret = array();
        }
        return $ret;
    }

    public function getUserNicknameByArr($arr){

        foreach($arr as $key => $value){
            $temp[$value['user_ybid']] = array();
        }
        $conds = '(ybid IN ('. implode( ',', array_keys($temp) ) .') )';

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_User', 'ybid, nickname', $conds);
        if(!$ret){
            return false;
        }

        foreach($ret as $key => $value){
            $temp[$value['ybid']] = $value['nickname'];
        }

        $temp = array_unique($temp);

        return $temp;
    }

    public function addUserPoint($user_ybid, $add, $type){
        $conds = array('ybid' => $user_ybid);

        switch($type){
            case 1:
                $fields = 'interpersonal';
                break;
            case 2:
                $fields = 'charm';
                break;
            case 3:
                $fields = 'figure';
                break;
        }

        if(empty($fields)){
            return false;
        }
        
        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_User', $fields, $conds);
        if(!$ret){
            return false;
        }

        $data = array($fields => ($ret[0][$fields] + $add) );

        $ret = $db->update('meet_User', $data, $conds);
        if(!$ret){
            return false;
        }

        return true;
    }

    public function addLabelLog($user_ybid, $arr){
        $db = Vera_Database::getInstance();
        $data = array(
            'user_ybid' => $user_ybid,
            'time' => date('Y-m-d H:i:s')
            );
        foreach($arr as $key => $value){
            $data['label_id'] = $value;
            $ret = $db->insert('meet_LabelLog', $data);
            if(!$ret){
                //记Log
            }
        }

        return true;
    }

    public function deleteLabelLog($user_ybid, $arr){
        $db = Vera_Database::getInstance();
        $conds = "user_ybid = $user_ybid AND label_id IN (" . implode(',', $arr) . ')';
        $ret = $db->delete('meet_LabelLog', $conds);
        if(!$ret){
            return false;
        }

        return true;
    }
}
?>