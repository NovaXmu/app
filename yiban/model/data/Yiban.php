<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Yiban.php
*	description:	易班网薪接口的操作
*
*	@author Nili
*	@license Apache v2 License
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
     * @param  int  	$id_yiban	用户易班id
     * @param  int  	$pay	   支付的网薪数量
     * @param  string   $access_token     授权凭证
     * @param   string  $msg            发网薪的原因
     * @return bool
     */
	public static function paySalary($id_yiban, $access_token, $pay, $msg = ''){
        if ($pay <= 0)
        {
            return true;
        }
        $payUrl = "https://openapi.yiban.cn/pay/yb_wx?access_token=" . $access_token . "&yb_userid=" . $id_yiban . "&pay=" . $pay;
        $res = self::sendRequest($payUrl);
        $tem = json_decode($res, true);//{"status":"success","info":true}
        $info = array('yb_uid' => $id_yiban, 'access_token' => $access_token, 'money' => -$pay, 'msg' => $msg);
        if (isset($tem['status']) && $tem['status'] == 'success')
        {
            $info['res'] = 'success';
            self::addYibanWxLog($info);
            return true;
        }
        $info['res'] =  $tem['info']['code'] . "," .$tem['info']['msgCN'];
        self::addYibanWxLog($info);
        return false;
	}

    /**
     * 发放网薪，学校账户发放给用户
     * @param  int      $id_yiban   用户易班id
     * @param  int      $award       获得的网薪数量
     * @param  string   $access_token     授权凭证
     * @return bool
     */
    public static function awardSalary($id_yiban, $access_token, $award, $msg = ''){
        if ($award <= 0)
        {
            return true;
        }
        $awardUrl = "https://openapi.yiban.cn/school/award_wx?access_token=" . $access_token . "&yb_userid=" . $id_yiban . "&award=" . $award;
        $res = self::sendRequest($awardUrl);
        $tem = json_decode($res, true);//{"status":"success","info":true}
        $info = array('yb_uid' => $id_yiban, 'access_token' => $access_token, 'money' => $award, 'msg' => $msg);
        if (isset($tem['status']) && $tem['status'] == 'success')
        {
            $info['res'] = 'success';
            self::addYibanWxLog($info);
            return true;
        }
        $info['res'] = $tem['info']['code'] . "," .$tem['info']['msgCN'];
        self::addYibanWxLog($info);
        return false;
    }

    /**
     * 网薪接口加日志信息
     * @param $info array
     */
    public static function addYibanWxLog($info){
        $app = explode('?', $_SERVER['REQUEST_URI']);
        $info['fromApi'] = $app[0];
        $info['time'] = date('Y-m-d H:i:s');
        $db = Vera_Database::getInstance();
        $db->insert('yiban_WxLog', $info);
    }

	/**
     * 获取易班token
     * @param  array  	$conf	配置文件数组，有AppID、AppSecret、CALLBACK
     * @return array    access_token和token_expires和userid
     */
	public static function getYibanToken($conf)
	{
        $ret = array('access_token' => '', 'token_expires' => '', 'userid' => ''); 
		if (isset($_GET["code"]))
		{
			$getTokenApiUrl = "https://oauth.yiban.cn/token/info?code=".$_GET['code']."&client_id={$conf['AppID']}&client_secret={$conf['AppSecret']}&redirect_uri={$conf['CALLBACK']}";
    		$res = self::sendRequest($getTokenApiUrl);
    		if(!$res){
    		    return $ret;
    		}
    		$userTokenInfo = json_decode($res,true);
            var_dump($userTokenInfo);
    		$ret['access_token'] = $userTokenInfo["access_token"];
    		$ret['token_expires'] = $userTokenInfo['expires'];
            $ret['userid'] = $userTokenInfo['userid'];//待定
		}

		if (isset($_GET["verify_request"]))
		{
			$postStr = pack("H*", $_GET["verify_request"]);
    		$postInfo = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $conf['AppSecret'], $postStr, MCRYPT_MODE_CBC, $conf['AppID']);
    		$postInfo = rtrim($postInfo);
    		$postArr = json_decode($postInfo, true);
    		if(!$postArr['visit_oauth'])
    		{
    			return $ret;
    		}
    		$ret['access_token'] = $postArr['visit_oauth']["access_token"];
    		$ret['token_expires'] = date('Y-m-d H:i:s',$postArr['visit_oauth']['token_expires']);
            $ret['userid'] = $postArr['visit_user']['userid'];
		}

		return $ret;
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

    public static function login($account, $password, &$ch, $ip = null)
    {
        $loginUrl = "https://www.yiban.cn/login/doLoginAjax";
        $ip = empty($ip) ? rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255) : $ip;
            $headers = array(
            "CLIENT-IP:$ip",
            "X-Forward-For:$ip"
        );
        $postData = array('account' => $account, 'password' => $password, 'captcha' => '');
        $opt = array(
            CURLOPT_URL             => $loginUrl,
            CURLOPT_HEADER          => 0,
            CURLOPT_TIMEOUT         => 20,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_COOKIEJAR       => "",
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $postData
        );
        curl_setopt_array($ch, $opt);
        $res = curl_exec($ch);

        $res = json_decode($res, true);
        if ($res['code'] != 200) {
            return false;
        }
        return true;
    }

    /**
     * 获取当前用户已加入的公共群
     * https://openapi.yiban.cn/group/public_group
     * GET请求,返回json
     * access_token	必填	用户授权凭证
     * page	选填	页码（默认1）
     * count	选填	每页数据量（默认15，最大30）
     */
    public static function getPublicGroup($access_token)
    {

        $api = "https://openapi.yiban.cn/group/public_group";
        $ch = curl_init();
        $page = 1;
        $count = 30;
        $res = array();
        $num = 0;
        do {
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
                CURLOPT_URL            => $api . "?access_token=$access_token&page=$page&count=$count",
            );
            curl_setopt_array($ch, $options);
            $jsonData = curl_exec($ch);
            $data = json_decode($jsonData, true);
            if ($data['status'] == 'error') {
                break;
            }
            $res = array_merge($res, $data['info']['public_group']);
            $num = $data['info']['num'];
            $page ++;
        } while ($page * $count < $data['info']['num']);
        return array('public_group' => $res, 'num' => $num);
    }
}
?>