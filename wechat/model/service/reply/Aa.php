<?php
/**
*
*   @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*   All rights reserved
*
*   file:           Aa.php
*   description:    动态回复Service层
*
*   @author Yuri
*   @license Apache v2 License
*
**/

/**
* 动态回复Service层
*/
class Service_Reply_Aa
{
    private static $resource = NULL;

    function __construct($_resource)
    {
        self::$resource = $_resource;
    }

    /**
     * 每日签到
     * @return  array
     */
    public function checkin()
    {
        $data = new Data_Reply_Aa(self::$resource);
        $isPay = $data->isPay();
        $num = $data->getNum();
        $temp['Articles'] = array();

        $today = strtotime('Today');
        $time = $today + 23400;
        // if (time() <= $time) {
        //     $temp['Articles'][0]['Title'] = "深夜啦，应该早些休息哦\n明早再来签到吧~";//图文信息大标题
        // }
        // else {
        $url = $_SERVER['HTTP_HOST'] . '/checkin';
        $temp['Articles'][0]['Title'] = "";//图文信息大标题
        $temp['Articles'][0]['PicUrl'] = "http://www.novaxmu.cn/templates/checkin/img/checkin_logo.png";//大图链接
        $temp['Articles'][0]['Url'] = $url;
        // }

        $temp['Articles'][1]['Title'] = "本月答题签到排行榜";//图文信息大标题
        $temp['Articles'][1]['Url'] = $_SERVER['HTTP_HOST'] . '/checkin/rank';

        $temp['Articles'][2]['Title'] = "上月答题签到排行榜";//图文信息大标题
        $temp['Articles'][2]['Url'] = $_SERVER['HTTP_HOST'] . '/checkin/rank?mode=lastMonth';

        if(!empty($isPay)){
            $temp['Articles'][3]['Title'] = '您有' . count($isPay) . '次答题签到奖励等待领取';//图文信息大标题
            $temp['Articles'][3]['Url'] = $_SERVER['HTTP_HOST'] . '/checkin/pay';
        }else{
            $temp['Articles'][3]['Title'] = '查看答题签到次数';//图文信息大标题
            $temp['Articles'][3]['Url'] = $_SERVER['HTTP_HOST'] . '/checkin/pay';
        }
        $ret['type'] = 'news';
        $ret['data'] = $temp;
        return $ret;
    }

    /**
     * 帐号绑定
     * @param  string $title 图文信息大标题
     * @return array        绑定用图文信息
     * @author nili
     */
    public function linkin($title = '')
    {
        $url = "http://www.novaxmu.cn/wap/linkin";
        $temp['Articles'] = array();
        $temp['Articles'][0]['Title'] = $title;
        $temp['Articles'][0]['PicUrl'] = "http://www.novaxmu.cn/templates/wap/img/linkin_logo.png";
        $temp['Articles'][0]['Url'] = $url ."?openid=". self::$resource['FromUserName'];

        $ret['type'] = 'news';
        $ret['data'] = $temp;
        return $ret;
    }


    /**抽奖活动，临时
     * 2015年9月幸运日活动
     * @author Nili <nl_1994@foxmail.com>
     * @return array 文本信息
     */
    public function temLuck($openid = '')
    {
        $ret['type'] = 'text';
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $arg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $openid = $arg['FromUserName'];
        
        $dbRes = Data_Db::checkLinkin($openid);
        if ($dbRes['code'] < 3)
        {
            $data['Content'] = "请绑定厦大账号及易班账号。";
        }
        else
        {
            if (time() < strtotime("2015-09-09 00:00:00") && 
                !in_array($dbRes['xmu_num'], array('23320122203966')))
            {
                $data['Content'] = "明天正式开始~";
                $ret['data'] = $data;
                return $ret;
            }
            $luckNum = substr($dbRes['xmu_num'], -1);
            if (date("d") % 10 != (int)$luckNum && !in_array($dbRes['xmu_num'], array('23320122203966')))
            {
                $data['Content'] = "今天不是你的幸运日，改天再来~";
                $ret['data'] = $data;
                return $ret;
            }
            else
            {
                $data['Content'] = $this->awardTmpLuck($dbRes);
            }
        }
        
        $ret['data'] = $data;
        return $ret;
    }

    /**
     * 幸运日辅助函数
     * @param  array $dbRes 身份信息，学号，易班id等
     * @return string        发网薪的msg
     * @author nili 
     */
    public function awardTmpLuck($dbRes)
    {
        if (Data_Db::getTodayLog($dbRes['yiban_uid']))
        {
            return "您今日抽奖次数已用完，下次再来~";
        }
        $award = (rand() % 5 + 1) * 100;
        $msg = "你抽中了{$award}网薪，";
        $validAccessToken = Data_Db::getValidAccessToken($dbRes['yiban_uid']);
        if (!$validAccessToken)
        {
            $msg .= "但是易班身份已过期，可以重新绑定再来抽奖~";
            return $msg;
        }
        Data_Db::insertLog($dbRes['yiban_uid'], $dbRes['xmu_num'], $award);

        $requestUri = $_SERVER['REQUEST_URI'];
        Vera_Autoload::setApp('yiban');
        $_SERVER['REQUEST_URI'] = '/wechat/tmpLuck';
        $awardRes = Data_Yiban::awardSalary($dbRes['yiban_uid'], $validAccessToken, $award);
        Vera_Autoload::setApp('wechat');
        $_SERVER['REQUEST_URI'] = $requestUri;
        if ($awardRes)
        {
            $msg .= '网薪已发放至您的账户。';
        }
        else 
        {
            $msg .= '未知原因发网薪失败，可截图找管理员找回~';
        }
        return $msg;
    }

    /**
     * 活动约吗
     * @return array
     */
    public function huodong()
    {
        $data = $this->_oldHuodong();//暂时使用以前的实现方式，等待大熊推荐完工
        $ret['type'] = 'news';
        $ret['data'] = $data;
        return $ret;
    }

    /**
     * 就业留言板
     * @return array
     */
    public function liuyanban()
    {
        $data = $this->_oldLiuyanban();//暂时使用以前的实现方式，等待大熊推荐完工
        $ret['type'] = 'news';
        $ret['data'] = $data;
        return $ret;
    }

    private function _oldHuodong()
    {
        $ret['Articles'] = array();

        $ret['Articles'][0]['Title'] = "【大熊推荐】帮推须知";
        $ret['Articles'][0]['PicUrl'] = "http://mmbiz.qpic.cn/mmbiz/PtXBRreRLdYf73kOpswRKZiaBZk3gmk5GDUk6nEVHT2Cd4libNXP4oDZ33DfaSYNlBBF4T0vEImaTOpYZhRn2sjg/0";
        $ret['Articles'][0]['Url'] = "http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201404193&idx=1&sn=a212d7bdf94b64623f492058db88ca54&key=c9d28ca78bee7cda85f47559e3c52ba294e1d48d281cf51ee66d57a9a4377f1b321f8f6402207a302535d372a4558619&ascene=0&uin=MjIzOTg3NDY2MQ%3D%3D&devicetype=iMac+MacBookAir5%2C2+OSX+OSX+10.10+build(14A389)&version=11010011&pass_ticket=Z4BQs0Yp%2B1LcWHT67tuyt%2B7a1rA%2FT%2BFPUv9rQMkPv5Zo3NzEXHW4uU4QPDQx9yQa";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201404193&idx=2&sn=8948b6a7bb46b09ab70dda5582c99cac';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][1]['Title'] = $result[0][0];
                $ret['Articles'][1]['PicUrl'] = "";
                $ret['Articles'][1]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201404193&idx=3&sn=39484951a41fe601b47b0a5e0c2e7bf0';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][2]['Title'] = $result[0][0];
                $ret['Articles'][2]['PicUrl'] = "";
                $ret['Articles'][2]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201404193&idx=4&sn=82f3676989e6cec6a4048fb4cd4da637';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][3]['Title'] = $result[0][0];
                $ret['Articles'][3]['PicUrl'] = "";
                $ret['Articles'][3]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201404193&idx=5&sn=dcdad873ca8360144af3d1c5922d2fe3';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][4]['Title'] = $result[0][0];
                $ret['Articles'][4]['PicUrl'] = "";
                $ret['Articles'][4]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201404193&idx=6&sn=b339f8e6b33ca5f95aeb8d3dfcead0aa';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][5]['Title'] = $result[0][0];
                $ret['Articles'][5]['PicUrl'] = "";
                $ret['Articles'][5]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        return $ret;
    }

    private function _oldLiuyanban()
    {
        $ret['Articles'] = array();

        $ret['Articles'][0]['Title'] = "【易专栏】就业\"刘\"言板";
        $ret['Articles'][0]['PicUrl'] = "http://mmbiz.qpic.cn/mmbiz/PtXBRreRLdZZG1GJeEdoawwdV5icia9Gyibrib8rz953icCWEPTzN9no397RzzY0ic88j0DPnG22xbPuzicQEqlZIfMlA/0";
        $ret['Articles'][0]['Url'] = "http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201384193&idx=1&sn=217a6cdd0744db9cf4479fccbc2c0dda&key=17469d17579ac7e08186da9dde775a504c14147bf490ffb401e1b10173316b60d60828266fb854cc6cb9b8df4b7011bd&ascene=0&uin=MjIzOTg3NDY2MQ%3D%3D&devicetype=iMac+MacBookAir5%2C2+OSX+OSX+10.10+build(14A389)&version=11010011&pass_ticket=2kXGK%2FMuauYm%2BZeKcvMfEMI%2BoXPfyzhN7xqrtkXfOPK014O0yA58o6gUuevdxdGA";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $url = "http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201384193&idx=2&sn=748551195b7f57758095c26680ed2315";
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][1]['Title'] = $result[0][0];
                $ret['Articles'][1]['PicUrl'] = "";
                $ret['Articles'][1]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201384193&idx=3&sn=daa10131c3fc2abbd2c61fa067c3ffd9';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][2]['Title'] = $result[0][0];
                $ret['Articles'][2]['PicUrl'] = "";
                $ret['Articles'][2]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201384193&idx=4&sn=7fc658de801242ed41beee006f811a6b';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][3]['Title'] = $result[0][0];
                $ret['Articles'][3]['PicUrl'] = "";
                $ret['Articles'][3]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201384193&idx=5&sn=386688f39b9f28a7eee68239ab20ef8a';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][4]['Title'] = $result[0][0];
                $ret['Articles'][4]['PicUrl'] = "";
                $ret['Articles'][4]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        $url = 'http://mp.weixin.qq.com/s?__biz=MjM5OTQ3MzgzMw==&mid=201384193&idx=6&sn=8c8756d645668a721b0233b2096f3ed8';
        curl_setopt($ch, CURLOPT_URL, $url);
        if($content = curl_exec($ch))
        {
            $pattern = "/(?<=(msg_title = \")).*(?=\")/";
            preg_match_all($pattern, $content, $result);
            if ($result[0][0] != '无') {
                $ret['Articles'][5]['Title'] = $result[0][0];
                $ret['Articles'][5]['PicUrl'] = "";
                $ret['Articles'][5]['Url'] = $url;
            }
            else{
                return $ret;
            }
        }
        else{
            return $ret;
        }

        return $ret;
    }
}

?>
