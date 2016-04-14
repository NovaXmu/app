<?php
/**
*
*   @copyright  Copyright (c) 2015 Nili
*   All rights reserved
*
*   file:           vote.php
*   description:    刷票
*
*   @author Nili
*   @license Apache v2 License
*   
**/
/**
*       刷票
*/
class Action_Api_Private_Vote
{
    
    function run()
    {
        $start = time();
        set_time_limit(0);
        $times = rand(40,60);
        $effectiveTimes = 0;
        $file = fopen('out', 'a+');
        fputs($file, date('Y-m-d H:i:s') . "\n");
        fputs($file, $times . "\n");
        while($times --) {
            sleep(rand(10,30));
            $effectiveTimes += $this->doVoteTask();
        }
        fputs($file, $effectiveTimes . "\n");
        fputs($file, time() - $start . "\n");
        fclose($file);
    }

    function doVoteTask() 
    {
        $optionNum = rand(1,9);
        while ($optionNum --) {
            $optionid[] = rand(32,90);
        }
        $optionid[] = 59;
        sort($optionid);
        $optionUrl = implode('&optionid[]=', $optionid);
        $url = "http://app.ggw.edu.cn/?app=vote&controller=vote&action=ajaxvote&contentid=5250&optionid[]=" . $optionUrl;
        $ip = rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255);
        $ch = curl_init();
        $headers = array(
            "CLIENT-IP:$ip",
            "User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36",
            "X-Forward-For:$ip",
            "Referer: http://www.ggw.edu.cn/zthd/WZPX/20151203-5250.shtml"
            );
        $opt = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT  => 10,
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            );

        curl_setopt_array($ch, $opt);
        $ret = curl_exec($ch);
        if (strlen($ret) > 500) {
            return 1;
        }
        return 0;
    }
}

