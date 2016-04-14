<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Base.php
*    description:     微信平台公用功能封装
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*
*/
class Data_Base
{
    public $accessToken;

    function __construct()
    {
        $conf = Vera_Conf::getConf('global');
        $conf = $conf['wechat'];
        $this->accessToken = $this->getAccessToken($conf['AppID'], $conf['AppSecret']);
    }

    /**
     * 获取 access_token
     * @param  string $appId     appID
     * @param  string $appSecret appSecret
     * @return string            access_token
     */
    public static function getAccessToken($appId, $appSecret)
    {
        if (!empty(self::$accessToken)) {
            return self::$accessToken;
        }

        $key = 'wechat_accesstoken';
        $cache = Vera_Cache::getInstance();
        $ret = $cache->get($key);
        if ($cache->getResultCode() == Memcached::RES_SUCCESS) {
            return $ret;
        }

        $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
        $url = sprintf($api, $appId, $appSecret);
        $handle = curl_init();
        $options = array(
                    CURLOPT_URL            => $url,
                    CURLOPT_HEADER         => 0,
                    CURLOPT_RETURNTRANSFER => 1
                    );
        curl_setopt_array($handle, $options);

        $content = curl_exec($handle);
        if (curl_errno($handle))//检查是否有误
            return false;
        curl_close($handle);
        $content = json_decode($content,true);

        $cache->set($key,$content['access_token'],$content['expires_in'] - 60);//保险起见减去60秒
        return $content['access_token'];
    }

    /**
     * 获取用户信息
     * @param   string $openID
     * @return array          用户信息数组
     */
    public function getUserInfo($openID)
    {
        $accessToken = $this->accessToken;

        $api = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN";
        $url = sprintf($api, $accessToken, $openID);
        $handle = curl_init();
        $options = array(
                    CURLOPT_URL            => $url,
                    CURLOPT_HEADER         => 0,
                    CURLOPT_RETURNTRANSFER => 1
                    );
        curl_setopt_array($handle, $options);

        $content = curl_exec($handle);//执行
        if (curl_errno($handle))//检查是否有误
            return false;
        curl_close($handle);

        return json_decode($content,true);
    }
}

?>
