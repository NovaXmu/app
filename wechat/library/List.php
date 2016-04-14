<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            List.php
*    description:     微信访问记录工具库
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  访问记录工具库
*/
class Library_List
{

    function __construct() {}

    /**
     * 添加一条微信访问记录
     * @param string $openid openid
     */
    public static function add($openid)
    {
        $time = time();
        Vera_Log::addLog('access',$openid.'|'.$time);
        return true;
    }

    /**
     * 获取记录
     * @param  integer $rows 个数
     * @return  array         访问记录
     */
    public static function get($rows = 1000)
    {
        $rows =  Vera_Log::readLog('access',$rows);
        foreach ($rows as &$row) {
            $row = explode('|', $row);
        }
        return $rows;
    }

    /**
     * 获取最近一段时间的访问记录
     * @param  integer $seconds 最近秒数，默认48小时
     * @return  array            近期访问的openid数组
     */
    public static function getRecent($seconds = 172800)
    {
        $limit = time() - $seconds;
        $temp = array();
        $array = array();
        $step = 3000;
        $top = NULL;

        //获取list
        while ($temp = Vera_Log::readLog('access',$step)) {
            if ($top == $temp[0]) {
                break;
            }
            $top = $temp[0];
            $split = explode('|', $top);
            if ($split[1] < $limit) {
                if (empty($array)) {
                    $array = $temp;
                    Vera_Log::addLog('array',json_encode(array('top' => $top, 'end' => array_pop($temp)))); 
                }
                break;
            }
            else {
                $array = $temp;
                $step+=$step;
                Vera_Log::addLog('array',json_encode(array('top' => $top, 'end' => array_pop($temp)))); 
            }
        }
        Vera_Log::addLog('array',"--------------------------------------------------");//分隔 

        $ret = array();
        //$array = $array[0];//神奇的Bug...时有时无...

        //排重
        foreach ($array as $each) {
            $each = explode('|', $each);
            if ($each[1] < $limit) {
                continue;
            }
            if (!in_array($each[0], $ret)) {
                $ret[] = $each[0];
            }
        }
        return $ret;
    }

    /**
     * 记录推送日志
     * @param  array $content  推送内容数组
     * @param  array $log      各用户推送结果
     */
    public static function addLog($content, $log)
    {
        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $buffer = PHP_EOL.'=='.date("Y-m-d H:i:s", time()).'=='.PHP_EOL;
        $buffer.= $content .PHP_EOL;
        $buffer.= '---'.PHP_EOL;

        $buffer.= json_encode($log, JSON_UNESCAPED_UNICODE);
        Vera_Log::addLog('push',$buffer);
    }
}

?>
