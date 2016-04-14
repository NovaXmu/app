<?php


class Action_Test{
    public function run(){
        var_dump('test start');
        //$this->modifyCheckin();
        var_dump('test done');
    }

    private function modifyCheckin(){
        $db = Vera_Database::getInstance();
        $list = $db->select('checkin_Log', '*', 'isPay = -1 OR isPay = 1');
        for($i = 0; $i<count($list); $i++){
            $rows = array(
                'money' => $this->getMoney($list[$i]['ID'], $list[$i]['question_ID']),
                'isPay' => $list[$i]['isPay']
                );
            $db->update('checkin_Log', $rows, array('id' => $list[$i]['ID']));
        }
        // $list = $db->select('checkin_Log', '*', array('isPay' => -1));
        // for($i = 0; $i<count($list); $i++){
        //     $rows = array(
        //         'money' => $this->getMoney($list[$i]['ID'], $list[$i]['question_ID'])
        //         );
        //     $db->update('checkin_Log', $rows, array('id' => $list[$i]['ID']));
        // }
    }

    private function getMoney($logId, $questionId){
        $db = Vera_Database::getInstance();
        $conds = "ID <= $logId and question_ID = $questionId";
        $count = $db->selectCount('checkin_Log',$conds);
        //可领取网薪数量
        switch($count){
                case 1:
                    return 50;
                case 2:
                case 3:
                case 4:
                case 5:
                    return 20;
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                    return 10;
                default:
                    return 5;
        }
    }

    private function second(){
        $db = Vera_Database::getInstance();
        $ybList = $db->select('vera_Yiban', '*', NULL, NULL, 'order by id desc');
        $errYiban = array();
        $errUser = array();
        foreach($ybList as $yb){
            //插入Yiban表
            $rows = array(
                'uid' => $yb['uid'],
                'accessToken' => $yb['access_token'],
                'expireTime' => $yb['expire_time'],
                );
            $ret = $db->insert('Yiban', $rows);
            if(!$ret){
                $errYiban[] = $yb;
            }
            //更新User表
            $rows = array();
            $conds = array('ybid' => $yb['uid']);
            $user = $db->select('meet_User', '*', $conds);
            if(!empty($user)){
                $rows['yibanNickname'] = $user[0]['nickname'];
                $rows['sex'] = $user[0]['sex'];
            }
            $rows['yibanUid'] = $yb['uid'];
            $conds = array('xmuId' => $yb['xmu_num']);
            $ret = $db->update('User', $rows, $conds);
            if(!$ret){
                $user['xmu_num'] = $yb['xmu_num'];
                $errUser[] = $user;
            }
        }
        return true;
    }

    private function first(){
        // if(!isset($_GET['start_id']) || !isset($_GET['end_id'])){
        //     echo '请输入数据';
        //     return false;
        // }
        // $start_id = $_GET['start_id'];
        // $end_id = $_GET['end_id'];

        $db = Vera_Database::getInstance();
        //$conds = "id >= $start_id && id < $end_id";
        $conds = NULL;
        $vera_userList = $db->select('vera_User', '*', $conds, NULL, 'order by id desc');
        $err = array();
        foreach($vera_userList as $user){
            $rows = array(
                'id' => $user['id'],
                'wechatOpenid' => empty($user['wechat_id'])?NULL:$user['wechat_id'],
                'xmuId' => empty($user['xmu_num'])?NULL:$user['xmu_num'],
                'xmuPassword' => $user['xmu_password'],
                'isLinkedXmu' => $user['xmu_isLinked'],
                'linkXmuTime' => $user['xmu_linkTime'],
                'yibanUid' => empty($user['yiban_uid'])?NULL:$user['yiban_uid'],
                'isLinkedYiban' => $user['yiban_isLinked'],
                'linkYibanTime' => $user['yiban_linkTime'],
                'realname' => $user['real_name'],
                'college' => $user['college'],
                'telephone' => $user['mobile_phone']
                );
            $ret = $db->insert('User', $rows);
            if(is_bool($ret)){
                $err[] = $user;
                $db->insert('error_User', $user);
            }
        }
        var_dump($err);
        return true;
    }
}
?>