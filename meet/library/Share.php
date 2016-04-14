<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Share.php
*   description:      基础的获取数据的数据层
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Library_Share{
    function __construct(){

    }

    const INT_DATA = 'int';
    const ARRAY_DATA = 'array';
    const FLOAT_DATA = 'float';
    const OTHER_DATA = 'other';

    public static function getRequest($key, $type = self::OTHER_DATA, $html = true){
        if(!isset($_REQUEST[$key])){
            return false;
        }

        //这里有可能取到非GET、POST数据，因为如果key在GET、POST中不存在，可能会去Cookie里面找，所以这里需要修改服务器的配置
        //禁止Request获取cookie，或者修改cookie的字段名，比如在cookie的每个字段名前面加上cookie_以区分
        $value = $_REQUEST[$key];

        switch($type){
            case self::INT_DATA:
                if(is_int($value)){
                    return $value;
                }else{
                    return intval($value);
                }
                break;
            case self::ARRAY_DATA:
                $value = json_decode($value, true);
                if(is_array($value)){
                    //需要对每个value做HTML过滤和addslashes
                    return $value;
                }
                break;
            case self::FLOAT_DATA:
                if(is_float($value)){
                    return $value;
                }else{
                    return floatval($value);
                }
                break;
            case self::OTHER_DATA:
                    $value = htmlspecialchars($value);
                    return addslashes($value);
                    
                break;
        }

        return false;
    }

    private static function getIP(){
        $ip = false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ips = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if($ip){
                array_unshift($ips, $ip);
                $ip = false;
            }
            for($i = 0; $i < count($ips); $i++)
                if(!eregi("^(10 | 172.16 | 192.168).", $ips[$i])){
                    $ip = $ips[$i];
                    break;
                }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    public static function isApi(){
        $url = $_SERVER['REQUEST_URI'];
        $temp = explode('/', $url);
        if(in_array('api', $temp))
            return true;
        return false;
    }

    public static function getLog($isApi = false, $errmsg = 'ok'){
        $url = $_SERVER['REQUEST_URI'];
        $temp = explode('/', $url);
        $log = array();
        $log['TIME'] = date('Y-m-d H:i:s');
        $log['IP'] = self::getIP();
        $log['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        $log['APP_NAME'] = $temp[1];
        if($isApi){
            $temp = explode('?', $temp[3]);
            $log['API_NAME'] = $temp[0];
            $log['Attribute'] = $temp[1];
            if(!empty($_POST)){
                $log['POST_DATA'] = $_POST;
            }
            $log['Errmsg'] = $errmsg;
        }else{
            $temp = explode('?', $temp[2]);
            $log['ACTION_NAME'] = $temp[0];
            $log['Attribute'] = $temp[1];
        }
        $log['USER_INFO'] = array(
                'ybid' => $_SESSION['yb_user_info']['yb_userid'],
                'username' => $_SESSION['yb_user_info']['yb_username'],
                'yb_studentid' => $_SESSION['yb_user_info']['yb_studentid'],
                'yb_schoolid' => $_SESSION['yb_user_info']['yb_schoolid'],
                'yb_schoolname' => $_SESSION['yb_user_info']['yb_schoolname']
                );
        return $log;  
    }

    
}
?>