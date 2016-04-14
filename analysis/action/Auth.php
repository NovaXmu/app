<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Auth.php
 *	description:	权限验证
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/

class Action_Auth extends Action_Base
{
    public static function run()
    {
        if (isset($_SESSION['id']))
        {
            return true;
        }
        if (isset($_SESSION['code']) && !empty($_SESSION['code']))
        {
            return true;
        }
        if (isset($_SESSION['auth']) && $_SESSION['auth'] == true)
        {
            return true;
        }

        $cache = Vera_Cache::getInstance();
        $key = 'cms_analysis_code_' . $_SESSION['id'];
        if (!isset($_REQUEST['code']))
        {
            $to = 'nl_1994@foxmail.com';
            $subject = 'nova后台数据';
            $code = rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
            $cache->set($key, $code, time() + 60*5);
            $message = '令牌如下:' . $code . '，五分钟内有效。';
            if (mail($to, $subject, $message))
            {
                echo '邮件已发送';
            }
            else echo '失败';
            return false;
        }

        $code = $cache->get($key);
        if (empty($code) || $code != $_REQUEST['code'])
        {
            echo '令牌已失效';
            return false;
        }
        $_SESSION['auth'] = true;
        return true;
    }
}