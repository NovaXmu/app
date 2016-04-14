<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/19
 * Time: 22:02
 * File: Participant.php
 * Description: 参与者service层
 */

class Service_Participant
{
    /**
     * 获取某活动详细信息，若缓存中取不到就从数据库中取并且组合
     * @param $actID
     * @return array|mixed|string   string表示错误信息，array则是正常数据
     */
    function getActDetail($actID)
    {
        $cache = new Data_Cache;
        $detail = $cache->getActDetail($actID);
        if (empty($detail)) {
            $data = new Data_Db(array('ID' => $_SESSION['user_id']));
            $detail = $data->getAct($actID);
            if (empty($detail)) {
                return '活动不存在';
            }
            $left = $data->getTicketLeft($detail);
            $detail['left'] = $left;
            $cache->setActDetail($actID, $detail);
        }
        return $detail;
    }

    function doTicketTask($actID, $userID)
    {
        $actDetail = $this->getActDetail($actID);
        if (is_string($actDetail)) {
            return $actDetail;//活动不存在
        }
        $now = date('Y-m-d H:i:s');
        if($actDetail['startTime'] > $now || $actDetail['endTime'] < $now) {
            return '非法抢票请求';
        }

        $data = new Data_Db(array('ID' => $userID));
        $userInAct = $data->getUserResultInAct($actID, $userID);
        if (isset($userInAct['result']) && $userInAct['result'] == 1) {
            return '已抢中，不可再抢';
        }

        $userUsedTime = $data->countOfUser($actID);
        if ($userUsedTime >= $actDetail['times']) {
            return '抢票次数已用完';
        }


        //检查条件到此为止，进入抢票
        $data->incrementCountOfUser($_POST['actID']);
        $func = new Data_Func();
        $res = $func->random($actDetail['chance']);
        if ($res['result'] != 1) {
            $data->saveRecord($actID, -1);
            return '没抢中';
        }

        $randKey = rand(1,5);//随机1-5个余票缓存中取数据
        if ($data->getOne($actDetail['actID'], $randKey) == 1)  {
            //取出一张票成功
            $data->saveRecord($actID, 1, $res['token']);
            return $res['token'];
        }
        $result = $data->getOneByTraversing($actDetail['actID'], $randKey);
        if ($result == 1){
            //取出一张票成功
            $data->saveRecord($actID, 1, $res['token']);
            return $res['token'];
        } else if ($result == -1) {
            //取出失败，但需要重置缓存
            $data->setAllLeftCache($actDetail);
            if ($data->getOne($actID, 5) == 1) {
                //重置缓存后从最多的那个key中取一张票，且取出成功
                $data->saveRecord($actID, 1, $res['token']);
                return $res['token'];
            } else {
                //从最多的key中取出一张票，失败，即无余票
                $data->saveRecord($actID, -1);
                return '余票不足';
            }
        } else {
            //遍历所有key取票失败，且所有key都存在不需要重置缓存，即无余票
            $data->saveRecord($actID, -1);
            return '余票不足';
        }
    }

}