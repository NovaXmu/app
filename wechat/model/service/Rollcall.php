<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Rollcall.php
*    description:     会场签到微信部分Service封装
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
* 会场签到Service封装
*/
class Service_Rollcall
{
    private static $resource = NULL;

    function __construct($resource)
    {
        self::$resource = $resource;
    }

    /**
     * @temp: 网络文化节临时代码，扫码得网薪
     */
    public function pay($id, $token)
    {
        $ret['type'] = 'text';
        $ret['data']['Content'] = '活动结束了哟~~';
        return $ret;
        //校验绑定
        $data = new Data_User(self::$resource);
        if (empty($accessToken = $data->getYibanAccess()) || $data->getYibanExpire() <= date("Y-m-d H:i:s")) { //未绑定易班帐号
            $url = "http://www.novaxmu.cn/mall";
            $temp['Articles'] = array();
            $temp['Articles'][0]['Title'] = "点击授权易班帐号后,请重新扫描二维码";
            $temp['Articles'][0]['PicUrl'] = "http://www.novaxmu.cn/templates/wap/img/linkin_logo.png";
            $temp['Articles'][0]['Url'] = $url;
            $ret['type'] = 'news';
            $ret['data'] = $temp;
            return $ret;
        }
        $uid = $data->getYibanUid();

        //校验token
        $cache = Vera_Cache::getInstance();
        $key = 'rollcall_temptoken_'.$id;
        if ($cache->get($key) != $token) {
            $ret['type'] = 'text';
            $ret['data']['Content'] = '异常操作';
            return $ret;
        }

        //同一个活动只允许获得一次网薪
        $content = file_get_contents(SERVER_ROOT.'data/temp/'.$id.'.data');
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $words = explode('|', $line);
            if (isset($words[0]) && $words[0] == $uid) {
                $ret['type'] = 'text';
                $ret['data']['Content'] = '已经玩过这个活动了哟，试试其他的吧~';
                return $ret;
            }
        }

        $yibanConf = Vera_Conf::getConf('yiban');
        $mallConf = $yibanConf['mall'];
        //发网薪
        $money = 150;//每扫一次支付5网薪，暂定
        Vera_Autoload::changeApp('yiban');
        $res = Data_Yiban::awardSalary($uid, $accessToken, $money);
        $userInfo = Data_Yiban::getYibanUserRealInfo($accessToken, $mallConf);//此处接口有变，会导致错误
        Vera_Autoload::reverseApp();
        if(!$res){
            $ret['type'] = 'text';
            $ret['data']['Content'] = '网薪支付失败，请再试一次';
            return $ret;
        }

        //清token
        $cache->delete($key);
        //记Log
        $db = Vera_Database::getInstance();
        $temp = $db->select('vera_Yiban', '*', array('uid'=>$uid,'fromApp'=>'mall'));
        $num = $temp[0]['xmu_num'];
        $log = $userInfo['yb_userid'] . '|' . $num . '|' . $userInfo['yb_realname'] .'|'. $userInfo['yb_userhead'] .'|'. date('Y-m-d H:i:s') ."\n";
        file_put_contents(SERVER_ROOT.'data/temp/'.$id.'.data', $log, FILE_APPEND);
        //计数
        $key = 'rollcall_temptoken_count_'.$id;
        $count = $cache->get($key) ? $cache->get($key) : 0;
        $cache->set(++$count, 3600 * 24);

        $ret['type'] = 'text';
        $ret['data']['Content'] = $money.'网薪已经发放到您的易班账户了哟~';
        return $ret;
    }

    public function checkin($act, $token)
    {
        $data = new Data_User(self::$resource);
        if ($data->isLink() != 1) { //未绑定厦大易班
            $service = new Service_Reply_Aa(self::$resource);
            $ret = $service->linkin('请绑定厦大帐号后重新扫码签到');
            return $ret;
        }
        $num = $data->getStuNum();

        Vera_Autoload::changeApp('rollcall');
        $service = new Service_Func();
        $checkin = $service->checkin($act, $token, $num);
        Vera_Autoload::setApp('wechat');


        if ($checkin['errno']) {
            $ret['type'] = 'text';
            $ret['data']['Content'] = $checkin['errmsg'];
            return $ret;
        }

        $ret['type'] = 'news';
        $temp['Articles'][0]['Title'] = "您是第{$checkin['rank']}位完成【{$checkin['actInfo']['name']}】签到 " . $checkin['awardMsg'];
        // $temp['Articles'][0]['Description'] = "签到者";
        $temp['Articles'][0]['PicUrl'] = 'http://www.novaxmu.cn/templates/rollcall/img/success.png';
        $temp['Articles'][0]['Url'] = 'http://q.yiban.cn/app/index/appid/1530';

        if (isset($checkin['actInfo']['extra'])) {
            if ($extra = json_decode($checkin['actInfo']['extra'], true)) {
                $temp['Articles'][0]['Url'] = $extra['Articles'][0]['Url'];
                foreach ($extra['Articles'] as $item) {
                    $temp['Articles'][] = $item;
                }
            }
        }
        $ret['data'] = $temp;
        return $ret;
    }
}
?>
