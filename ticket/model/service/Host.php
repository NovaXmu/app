<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/4/4
 * Time: 14:29
 * File: Host.php
 * Description:
 */

class Service_Host
{
    function exchange($actID, $token)
    {
        $data = new Data_Db();
        //检查token合法
        $log = $data->getLogByEffectiveToken($actID, $token);
        if (empty($log)) {
            throw new Exception('没有抢中记录', -1);
        }
        $user = $data->getUserByUserID($log[0]['userID']);
        if (empty($user)) {
            throw new Exception('用户非法', -1);
        }
        if ($log[0]['isUsed'] == 0) {
            $data->setTokenUsed($log[0]['id']);
        }
        $ret = array('xmuId' => $user[0]['xmuId'], 'realname' => $user[0]['realname'], 'logId' => $log[0]['id'], 'isUsed' => $log[0]['isUsed'], 'seat' => $log[0]['seat']);
        return $ret;
    }

    function assignSeat($logId, $seat)
    {
        $db = new Data_Db();
        $log = $db->getLog($logId);
        if (empty($log)) {
            return '非法请求';
        } else if ($log[0]['result'] != 1) {
            return "不能为未抢中的用户指定席位";
        }
        if (!$db->checkSeatIllegal($log[0]['actID'], $seat)){
            return '该座位已被指定给其他用户，请核对票据重新指定座位';
        }
        $db->assignSeat($logId, $seat);
        return '';
    }
}