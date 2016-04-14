<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Date: 2016/1/23
 * Time: 19:20
 */

class Service_YibanRank
{
    public $ch;
    function __construct()
    {
        $this->ch = curl_init();
        Vera_Autoload::changeApp('yiban');
        Data_Yiban::login('15711505721', 'novagzsyb', $this->ch);//工作室公众号账号，暂定用这个模拟登陆
        Vera_Autoload::reverseApp();
    }


    /**
     * 获取今日易班明星个人排行榜数据,需模拟登录
     * http://www.yiban.cn/forum/star/index?source=1&identity=1&offset=0&limit=20
     */
    function getStarRankData()
    {
        $res = array();
        $starRankData = Data_Cache::getCache('starRankData');
        if ($starRankData) {
            return json_decode($starRankData, true);
        }
        $url = "http://www.yiban.cn/forum/star/index";
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, false);
        $str = curl_exec($this->ch);
        preg_match_all('|http://img02.*?/avatar/user/200|', $str, $imgs);
        preg_match_all('|<b class="uname">.*?</b>|', $str, $names);
        preg_match_all('|日活跃度：.*?</b>|', $str, $actives);
        foreach ($imgs[0] as $index => $row) {
            $res[$index]['img'] = $row;
            $id = explode('/', $row);
            $res[$index]['yb_userid'] = $id[3];
            $res[$index]['name'] = strip_tags($names[0][$index]);
            preg_match_all('|(\d){1,10}|', $actives[0][$index], $active);
            $res[$index]['active'] = $active[0][0];
        }

        $cache = Vera_Cache::getInstance();
        $cache->set('mall_starRankData', json_encode($res), strtotime(time() + 3600));
        return $res;
    }

    /**
     * 获取今日易班班级EGPA日上升榜数据
     * http://www.yiban.cn/mobile/medal/type/0/kind/2
     */
    function getMedalRankData()
    {
        $res = array();
        $medalRankData = Data_Cache::getCache('medalRankData');
        if ($medalRankData) {
            $res = json_decode($medalRankData, true);
            return $res;
        }
        $url = "http://www.yiban.cn/mobile/medal/type/0/kind/2";
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, false);
        $str = curl_exec($this->ch);
        preg_match_all('|http://img02.*?/group/b|', $str, $imgs);
        preg_match_all('|<p class="name"><a href="/group.*?</p>|', $str, $names);
        preg_match_all('|学校.*?</p>|', $str, $schools);
        preg_match_all('|EGPA.*?</p>|', $str, $egpas);
        foreach ($imgs[0] as $index => $row) {
            $res[$index]['img'] = $row;
            $id = explode('/', $row);
            $res[$index]['group_id'] = $id[3];

            $res[$index]['name'] = strip_tags($names[0][$index]);

            $school = explode('：', $schools[0][$index]);
            $res[$index]['school'] = strip_tags($school[1]);

            $egpa = explode('：', $egpas[0][$index]);
            $res[$index]['egpa'] = strip_tags($egpa[1]);
        }
        $cache = Vera_Cache::getInstance();
        $cache->set('mall_medalRankData', json_encode($res), strtotime(time() + 3600));
        return $res;
    }
}