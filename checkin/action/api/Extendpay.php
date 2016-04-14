<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Extendpay.php
*   description:      Action for Extendpay.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Api_Extendpay extends Action_Base{
    function __construct() {}

    public function run(){
        $resource = $this->getResource();
        $num = $resource['num'];

        if(!$num){
            $ret['errno'] = 1;
            $ret['errmsg'] = '未绑定厦大身份，无法获得网薪， 快去绑定吧~ 记得回来领取网薪哦~';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        $yibanInfo = Data_Db::getYibanInfoByXmuNum($num);
        if (!$yibanInfo['yiban_islinked'])
        {   
            $ret['errno'] = 1;
            $ret['errmsg'] = '未绑定易班身份，无法获得网薪，快去绑定吧~ 记得回来领取网薪哦~';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        if ($yibanInfo['expire_time'] < date('Y-m-d H:i:s'))
        {
            $ret['errno'] = 1;
            $ret['errmsg'] = '易班身份已过期，无法获得网薪，快去重新授权吧~ 记得回来领取网薪哦~';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        //根据排名获得网薪
        $data = new Data_Db($resource);
        $count= $data->isPayForExtend();
        if($count <= 0){
            $ret['errno'] = '1';
            $ret['errmsg'] = '您已经领取过网薪，不可重复领取哦~';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }else{
            $pay = $data->setAllToPay();
        }

        //发网薪
        $yibanConf = Vera_Conf::getConf('yiban');
        $novaConf = $yibanConf['nova'];

        Vera_Autoload::changeApp('yiban');
        $res = Data_Yiban::awardSalary($yibanInfo['yiban_uid'], $yibanInfo['access_token'], $pay);
        //$userInfo = Data_Yiban::getYibanUserRealInfo($accessToken, $mallConf);//此处接口有变，会导致错误
        Vera_Autoload::reverseApp();

        if(!$res){
            $ret['errno'] = '1';
            $ret['errmsg'] = '网薪支付失败，请再试一次';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        //记Log
        // $db = Vera_Database::getInstance();
        // $temp = $db->select('vera_Yiban', '*', array('uid'=>$uid,'fromApp'=>'mall'));
        // $num = $temp[0]['xmu_num'];
        // $log = $userInfo['yb_userid'] . '|' . $num . '|' . $userInfo['yb_realname'] .'|'. $userInfo['yb_userhead'] .'|'. date('Y-m-d H:i:s') ."\n";
        // file_put_contents(SERVER_ROOT.'data/temp/'.$id.'.data', $log, FILE_APPEND);
        
        $data->addPayLog($pay);

        $ret['errno'] = '0';
        $ret['errmsg'] = $pay.'网薪已经发放到您的易班账户了哟~';
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
?>