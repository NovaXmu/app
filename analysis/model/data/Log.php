<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Log.php
 *	description:	data层日志处理，用于不同日志文件提取每行数据
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/

class Data_Log
{
    /**
     * @param $line 获取nginx access日志的行信息
     * @return array
     * @author nili
     */
    public static function getAccessLineInfo($line)
    {
        $arr = explode(' ', $line);
        $res['remoteIp'] = $arr[0];
        $res['time'] = date('Y-m-d H:i:s', strtotime(substr($arr[3] . ' ' . $arr[4], 1, -1)));
        $res['method'] = $arr[5];
        $tmp = explode('?', $arr[6]);
        $res['requestUrl'] = $tmp[0];
        $res['param'] = isset($tmp[1]) ? $tmp[1] : '';
        $res['protocol'] = $arr[7];
        $res['statusCode'] = $arr[8];
        $res['body_byte_sent'] = $arr[9];
        $res['http_referer'] = isset($arr[10]) ? $arr[10] : '';
        $res['user_agent'] = '';
        if (isset($arr[11]))
        {
            // $tmp  = strtok($arr[11], "\"/");
            $res['user_agent'] = implode(" ", array_slice($arr, 11));
        }
        return $res;
    }

    /**
     * 获取易班tmp.log的信息，临时用
     * @param  string $line 行
     * @return array       信息
     */
    public function getYibanTmpLineInfo($line) 
    {
        if ($arr = json_decode($line, true)) {
            return $arr;
        }
        return array();
    }
}