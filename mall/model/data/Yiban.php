<?php
/**
*
*   @copyright  Copyright (c) 2015 Nili
*   All rights reserved
*
*   file:           Yiban.php
*   description:    易班网薪接口的操作
*
*   @author Nili
*   @license Apache v2 License
*
**/

/**
*
*/
class Data_Yiban
{

    function __construct()
    {

    }

    /**
     * 网薪支付，用户支付给易班
     * @param  int      $id_yiban   用户易班id
     * @param  int      $pay       支付的网薪数量
     * @param  string   $access_token     授权凭证
     * @return bool
     */
    public static function paySalary($id_yiban, $access_token, $pay){
        if ($pay <= 0)
        {
            return true;
        }
        $payUrl = "https://openapi.yiban.cn/pay/yb_wx?access_token=" . $access_token . "&yb_userid=" . $id_yiban . "&pay=" . $pay;
        $res = self::sendRequest($payUrl);
        self::addYibanWxLog($id_yiban, $access_token, -$pay, json_encode(json_decode($res, true), JSON_UNESCAPED_UNICODE));
        $tem = json_decode($res, true);//{"status":"success","info":true}
        if (isset($tem['status']) && $tem['status'] == 'success')
        {
            return true;
        }
        return false;
    }

    /**
     * 发放网薪，学校账户发放给用户
     * @param  int      $id_yiban   用户易班id
     * @param  int      $award       获得的网薪数量
     * @param  string   $access_token     授权凭证
     * @return bool
     */
    public static function awardSalary($id_yiban, $access_token, $award){
        if ($award <= 0)
        {
            return true;//发0网薪时日志不保留
        }
        $awardUrl = "https://openapi.yiban.cn/school/award_wx?access_token=" . $access_token . "&yb_userid=" . $id_yiban . "&award=" . $award;
        $res = self::sendRequest($awardUrl);
        self::addYibanWxLog($id_yiban, $access_token, $award, json_encode(json_decode($res, true), JSON_UNESCAPED_UNICODE));
        $tem = json_decode($res, true);
        if (isset($tem['status']) && $tem['status'] == 'success')
        {
            return true;
        }
        return false;
    }

    /**
     * 网薪接口加日志信息
     * @param  int      $id_yiban   用户易班id
     * @param  int      $money       用户支付或收入的网薪数量，支出为负收入为正
     * @param  string   $access_token     授权凭证
     * @param  string   $res            易班返回的信息，成功或失败
     * @return bool
     */
    public static function addYibanWxLog($id_yiban, $access_token, $money, $res, $app = 'mall'){
        $content = "time[". date("Y-m-d H:i:s") ."] ";
        $content.= "IP[" . $_SERVER["REMOTE_ADDR"] ."] ";
        $content.= "yb_userid[" . $id_yiban ."] ";
        $content.= "access_token[" . $access_token ."] ";
        $content.= "money[" . $money ."] ";
        $content.= "res[" . $res ."] ";
        $content.= 'from[' . $app . '] ';
        $content.= PHP_EOL;
        Vera_Log::addLog('yibanMoney', $content);
    }

    /**
     * 获取易班token
     * @param  array    $conf   配置文件数组，有AppID、AppSecret、CALLBACK
     * @return array    access_token和token_expires
     */
    public static function getYibanTokenAndExpireTime($conf)
    {
        $ret = array('access_token' => '', 'token_expires' => '');
        if (isset($_GET["verify_request"]))
        {
            $postStr = pack("H*", $_GET["verify_request"]);
            $postInfo = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $conf['AppSecret'], $postStr, MCRYPT_MODE_CBC, $conf['AppID']);
            $postInfo = rtrim($postInfo);
            $postArr = json_decode($postInfo, true);
            if(!$postArr['visit_oauth'])
            {
                //return $ret;
                header("Location: https://openapi.yiban.cn/oauth/authorize?client_id={$conf['AppID']}&redirect_uri={$conf['CALLBACK']}&display=web");
                exit();
            }
            $ret['access_token'] = $postArr['visit_oauth']["access_token"];
            $ret['token_expires'] = date("Y-m-d H:i:s",$postArr['visit_oauth']['token_expires']);
        }

        return $ret;
    }

    /**
     * 获取易班用户实名信息
     * @param  string   access_token 用户易班token
     * @param  array   conf         配置文件数组，有AppID、AppSecret、CALLBACK
     * @return array    用户实名信息
     */
    public static function getYibanUserRealInfo($access_token)
    {
        $userInfoJsonStr = self::sendRequest("https://openapi.yiban.cn/user/real_me?access_token={$access_token}");
        $userInfo = json_decode($userInfoJsonStr,true);

        if (isset($userInfo['status']) && $userInfo['status'] == 'success')
        {
            return $userInfo['info'];
            /*unset($_SESSION['access_token']);//access_token错误，获取不到用户信息
            header("Location: https://openapi.yiban.cn/oauth/authorize?client_id={$conf['AppID']}&redirect_uri={$conf['CALLBACK']}&display=web");
            exit();*/
        }
        return array();

    }

    public static function sendRequest($url)
    {
        $handle = curl_init();
        $options = array(
                    CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
                    CURLOPT_USERAGENT       => 'Yi OAuth2 v0.1',
                    CURLOPT_CONNECTTIMEOUT  => 30,
                    CURLOPT_TIMEOUT         => 30,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_SSL_VERIFYPEER  => false,
                    CURLOPT_HEADER          => false,
                    CURLOPT_HTTPHEADER      => array(),
                    CURLINFO_HEADER_OUT     => true,
                    CURLOPT_URL            => $url
                    );
        curl_setopt_array($handle, $options);
        return curl_exec($handle);
    }
}
?>
