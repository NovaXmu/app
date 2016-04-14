<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             User.php
*   description:      Action for User.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Data_User{
    function __construct(){}

    public function getLocationList($conds = NULL){
        $db = Vera_Database::getInstance();
        return $db->select('anniversary_Ip', 'latitude, longitude', $conds);
    }

    public function getAuditerList($conds = NULL){
        $db = Vera_Database::getInstance();
        return $db->insert('anniversary_Auditer', '*', $conds);
    }

    public function addAuditer($xmuId){
        $db = Vera_Database::getInstance();
        return $db->insert('anniversary_Auditer', array('xmuId' => $xmuId));
    }

    public function addIp(){
        $ip = $_SERVER['REMOTE_ADDR'];
        $content = file_get_contents("http://getcitydetails.geobytes.com/GetCityDetails?fqcn=$ip");
        $json = json_decode($content);
        $rows['ip'] = $ip;
        $rows['country'] = $json->{'geobytescountry'};
        if($json->{'geobytescountry'} == 'China'){
            $content = file_get_contents("http://api.map.baidu.com/location/ip?ak=7IZ6fgGEGohCrRKUE9Rj4TSQ&ip={$ip}&coor=bd09ll");
            $json = json_decode($content);
            $rows['longitude'] = $json->{'content'}->{'point'}->{'x'};
            $rows['latitude'] = $json->{'content'}->{'point'}->{'y'};
            $rows['location'] = $json->{'content'}->{'address'};
        }else{
            $rows['longitude'] = $json->{'geobyteslongitude'};
            $rows['latitude'] = $json->{'geobyteslatitude'};
        }
        $db = Vera_Database::getInstance();
        return $db->insert('anniversary_Ip', $rows);
    }

    public function isAuditerByOpenid($openid){
        $conds = array('wechatOpenid' => $openid);
        $db = Vera_Database::getInstance();
        $ret = $db->select('User', 'xmuId', $conds);
        if(empty($ret))
            return array();
        $auditer = $db->select('anniversary_Auditer', '*', array('xmuId' => $ret[0]['xmuId'], 'isDelete' => -1));
        if(empty($auditer)){
            return false;
        }
        return true;
    }

    public function updateAuditer($xmuId, $isDelete){
        $db = Vera_Database::getInstance();
        return $db->update('anniversary_Auditer', array('isDelete' => $isDelete), array('xmuId' => $xmuId));
    }
}
?>