<?php
/**
*	@copyright
*
*	file:      Auth.php
*	description：权威性验证
*
*	@author linjun
*/

class Action_Auth extends Action_Base{

	function __construct(){}

	public static function run(){
		switch(ACTION_NAME){
			case 'Index':
				return true;
				break;
			case 'Api_Act':
                echo 'Api_Act';
                return true;
				//return self::_actInfo();
				break;
			case 'Api_Pro':
                echo 'Api_Pro';
                return true;
				//return self::_proInfo();
				break;
			case 'Api_Vote':
                echo 'Api_Vote';
                return true;
				//return self::_vote();
				break;
			default:
				return true;
				break;
		}
	}

	private static function _act()
    {
    	if (!isset($_GET['actID']) || !is_numeric($_GET['actID'])) {
            return false;
        }
        $resource['actID'] = intval($_GET['actID']);
        parent::setResource($resource);
        return true;
    }

    private static function _pro(){
    	if (!isset($_GET['proID']) || !is_numeric($_GET['proID'])) {
            return false;
        }
        if (!isset($_GET['actID']) || !is_numeric($_GET['actID'])) {
            return false;
        }
        $resource['proID'] = intval($_GET['proID']);
        $resource['actID'] = intval($_GET['actID']);
        parent::setResource($resource);
        return true;
    }

    private static function _vote(){
    	if (!isset($_GET['proID']) || !is_numeric($_GET['proID'])) {
            return false;
        }
        if (!isset($_GET['actID']) || !is_numeric($_GET['actID'])) {
            return false;
        }
        if (!isset($_GET['xmu_num']) || !is_numeric($_GET['xmu_num'])) {
            return false;
        }
        $resource['proID'] = intval($_GET['proID']);
        $resource['actID'] = intval($_GET['actID']);
        $resource['xmu_num'] = intval($_GET['xmu_num']);
        $resource['openid'] = isset($_GET['openid'])? $_GET['openid'] : '';
        parent::setResource($resource);
        return true;
    }

    private static function second(){
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

    private static function first(){
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
        return true;
    }
}
?>