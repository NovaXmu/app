<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Access.php
 *	description:    nginx access.log日志分析
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/
class Service_Access
{
    private static $_logDir;
    public $apps;
    public $actions;
    public $app;

    function __construct()
    {
        self::$_logDir = SERVER_ROOT . 'log/';
        $this->apps = array('cms', 'checkin', 'mall', 'meet', 'ticket', 'rollcall', 'wap', 'wechat');
        $this->actions = array(
            'wap' => array(
                //'api' => 'api',
                'linkin' => 'linkin',
                'teacher' => 'teacher',
                'rank' => 'rank',
                'sport' => 'sport',
                'together' => 'together'),
            // 'mall' => array(
            //     'auction' => 'auction',
            //     'earn' => 'earn',
            //     'exchange' => 'exchange',
            //     'index' => 'index',
            //     'person' => 'person',
               // 'api' => 'api'
            );
        foreach ($this->apps as $key => $value) {
            $this->app[$value] = 0;
        }
        foreach ($this->actions as $appName => $actionsName) {
            foreach ($actionsName as $actionName) {
                $this->app[$appName . '/' . $actionName] = 0;
            }
        }
    }

    /**
     * 统计时间区间内的数据
     * @param $startDate
     * @param $endDate
     * @return array 对应的access文件名
     * @author nili
     */
    public function getLogFile($startDate, $endDate)
    {
        $startTimeStamp = strtotime($startDate);
        $endTimeStamp = strtotime($endDate);

        $startWeek = date('Ymd', $startTimeStamp - 7 * 3600 * 24); //起始时间的上一周
        $endWeek = date('Ymd', $endTimeStamp + 7 * 3600 * 24);//结束时间的下一周

        $handle = opendir(self::$_logDir);
        $res = array();
        while (false !== ($file = readdir($handle)))
        {
            if (!preg_match('/access.log-/', $file))
            {
                continue;
            }
            $fileDate = substr($file, strlen('access.log-'), 8);
            if ($fileDate <= $endWeek && $fileDate >= $startWeek)
            {
                $res[] = $file;
            }
        }
        array_push($res,'access.log');//当前access.log时间区间不好确定，所以都加上access.log,之后分析时再按照时间判断
        return $res;
    }

    /**
     * 基本信息统计，某时间区间内不同app点击量
     * @param $startDate
     * @param $endDate
     * @author nili
     */
    public function basicInfo($startDate  = '', $endDate = '')
    {
        $endDate = $endDate ? $endDate : date("Y-m-d");
        $startDate = $startDate ? $startDate : '2015-07-05';//这个特殊的日子是因为nginx之前的日志被清了。。。
        $res = $this->app;
        $files = $this->getLogFile($startDate, $endDate);
        foreach ($files as $fileName){
            $handle = gzopen(self::$_logDir . $fileName, 'r');
            while ($line = fgets($handle)){
                $info = Data_Log::getAccessLineInfo($line);
                if (strtotime($info['time']) < strtotime($startDate)){
                    continue;
                }
                if (strtotime($info['time']) > strtotime($endDate)){
                    break;
                }
                $app = explode('/', $info['requestUrl']);
                if (isset($app[1]) && in_array($app[1], $this->apps)){
                    $res[$app[1]] = isset($res[$app[1]]) ? $res[$app[1]] + 1 : 1;
                    if (isset($this->actions[$app[1]]) && $app[2] != 'api'){
                        if (isset($app[2]) && in_array($app[2], $this->actions[$app[1]])){
                            $res[$app[1] . '/' . $app[2]] = isset($res[$app[1] . '/' . $app[2]]) ? $res[$app[1] . '/' . $app[2]] + 1 : 1;
                        }else if (empty($app[2])) {
                            $res[$app[1] . '/' . 'index'] = isset($res[$app[1] . '/' . 'index']) ? $res[$app[1] . '/' . 'index'] + 1 : 1;
                        }
                    }
                }
            }
            gzclose($handle);
        }
        return $res;
    }

    /**
     * 基本信息统计，按天统计浏览量
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @author nili
     */
    public function basicInfoByDay($startDate = '',$endDate = '')
    {
        $endDate = $endDate ? $endDate : date("Y-m-d");
        $startDate = $startDate ? $startDate : $this->addDay($endDate, -30);
        $totalDays = $this->getDaysBetweenDates($startDate , $endDate);
        if ($totalDays > 30)
        {
            return '最多只能查30天记录';
        }

        $res = array();
        foreach ($this->apps as $key => $app) {
            for ($date = strtotime($startDate); $date < strtotime($endDate); $date += 86400) {
                $res[date('Y-m-d', $date)][$app] = 0;
            }
        }
        $files = $this->getLogFile($startDate, $endDate);
        foreach($files as $fileName)
        {
            $handle = gzopen(self::$_logDir . $fileName, 'r');
            while ($line = fgets($handle))
            {
                $info = Data_Log::getAccessLineInfo($line);
                if (strtotime($info['time']) < strtotime($startDate))
                {
                    continue;
                }
                if (strtotime($info['time']) > strtotime($endDate))
                {
                    break;
                }
                $date = date("Y-m-d", strtotime($info['time']));
                $app = explode('/', $info['requestUrl']);
                if (isset($app[1]) && in_array($app[1], $this->apps) && !empty($date)) {
                    $res[$date][$app[1]] = isset($res[$date][$app[1]]) ? $res[$date][$app[1]] + 1 : 1;
                }
            }
            gzclose($handle);
        }
        return $res;
    }

    /**
     * 按小时获取某天信息
     * @param $date
     * @return array
     * @author nili
     */
    public function basicInfoByHour($date = '')
    {
        $date = $date ? $date : date("Y-m-d");
        $files = $this->getLogFile($date, $date);
        $res = array();
        foreach ($this->apps as $app) {
            for ($i=0; $i < 24; $i++) { 
                $res[$i][$app] = 0;
            }
        }
        foreach($files as $fileName){
            $handle = gzopen(self::$_logDir . $fileName, 'r');
            while($line = fgets($handle))
            {
                $info = Data_Log::getAccessLineInfo($line);
                if (strtotime($date. " 24:00:00") < strtotime($info['time']))
                {
                    break;
                }
                if (strtotime($date) > strtotime($info['time']))
                {
                    continue;
                }
                $hour = (int)date("H", strtotime($info['time']));
                $app = explode('/', $info['requestUrl']);
                if (isset($app[1]) && in_array($app[1], $this->apps) && !empty($hour)) {
                    $res[$hour][$app[1]] = isset($res[$hour][$app[1]]) ? $res[$hour][$app[1]] + 1 : 1;
                }
            }
        }
        return $res;
    }

    /**
     * 获取某时间段内使用各浏览器访问的数量，与之前的basicInfo基本相同
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @author nili
     */
    public function basicBrowserInfo($startDate = '', $endDate = '')
    {
        $endDate = $endDate ? $endDate : date("Y-m-d");
        $startDate = $startDate ? $startDate : '2015-07-05';//这个特殊的日子是因为nginx之前的日志被清了。。。
        $browser = array();
        $files = $this->getLogFile($startDate, $endDate);
        foreach ($files as $fileName) {
            $handle = gzopen(self::$_logDir . $fileName, 'r');
            while ($line = fgets($handle)) {

                $info = Data_Log::getAccessLineInfo($line);
                if (strtotime($info['time']) < strtotime($startDate)) {
                    continue;
                }
                if (strtotime($info['time']) > strtotime($endDate)) {
                    break;
                }
                $browser[$info['user_agent']] = isset($browser[$info['user_agent']]) ? $browser[$info['user_agent']] + 1 : 1;
            }
            gzclose($handle);
        }
        return $browser;
    }

    /**
     * 基本信息统计，按天统计各浏览器浏览量
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @author nili
     */
    public function basicBrowserInfoByDay($startDate = '',$endDate = '')
    {
        $endDate = $endDate ? $endDate : date("Y-m-d");
        $startDate = $startDate ? $startDate : $this->addDay($endDate, -30);
        $totalDays = $this->getDaysBetweenDates($startDate , $endDate);
        if ($totalDays > 30)
        {
            return '最多只能查30天记录';
        }

        $browser = array();
        $files = $this->getLogFile($startDate, $endDate);
        foreach($files as $fileName)
        {
            $handle = gzopen(self::$_logDir . $fileName, 'r');
            while ($line = fgets($handle))
            {
                $info = Data_Log::getAccessLineInfo($line);
                if (strtotime($info['time']) < strtotime($startDate))
                {
                    continue;
                }
                if (strtotime($info['time']) > strtotime($endDate))
                {
                    break;
                }
                $date = date("Y-m-d", strtotime($info['time']));
                $browser[$date][$info['user_agent']] = isset($browser[$date][$info['user_agent']]) ? $browser[$date][$info['user_agent']] + 1 : 1;
            }
            gzclose($handle);
        }
        return $browser;
    }

    /**
     * 根据日期计算几日前或几日后
     * @param $date
     * @param $addDay,为正，几日后，为负则几日前
     * @return bool|string
     * @author nili
     */
    public function addDay($date, $addDay)
    {
        return date("Y-m-d H:i:s", strtotime($date) + $addDay * 86400);
    }

    /**
     * 获取两个日期间相差的天数
     * @param $date1
     * @param $date2
     * @return number
     * @author nili
     */
    public function getDaysBetweenDates($date1, $date2)
    {
        return abs((strtotime($date1) - strtotime($date2)) / 86400);
    }
}

?>