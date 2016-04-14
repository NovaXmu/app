<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Nginx.php
 *	description:	Nginx日志分析相关
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/
/*
 *
 */
class Action_Nginx extends Action_Base
{
    private static $_service;
    public $startDate;
    public $endDate;

    public function __construct()
    {
        self::$_service = new Service_Access();
        $this->startDate = isset($_GET['start']) ? $_GET['start'] : '';
        $this->endDate = isset($_GET['end']) ? $_GET['end'] : '';
    }

    public function run()
    {
        set_time_limit(0);
        if (isset($_GET['m']))
        {
            switch ($_GET['m'])
            {
                case 'app':
                    return $this->appAnalysis();
                case 'browser':
                    return $this->browserAnalysis();
                default :
                    return 0;
            }
        }
        return $this->appAnalysis();
    }

    public function appAnalysis()
    {
        $ret = array('errno' => 0, 'errmsg' => 'ok', 'data' => array());
        if (isset($_GET['type']))
        {
            switch ($_GET['type'])
            {
                case 'hour':
                    $date = date('Y-m-d');
                    if (isset($_GET['date']) && !empty($_GET['date'])){
                        $date = $_GET['date'];
                    }
                    $data = self::$_service->basicInfoByHour($date);
                    break;
                case 'day':
                    $data = self::$_service->basicInfoByDay($this->startDate, $this->endDate);
                    break;
                default:
                    $data = self::$_service->basicInfo($this->startDate, $this->endDate);
            }
        }
        else
        {
            $data = self::$_service->basicInfo($this->startDate, $this->endDate);
        }
        if (is_array($data))
        {
            $ret['data'] = $data;
        } 
        else
        {
            $ret['errmsg'] = $data;
            $ret['errno'] = 1;
         }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return 0;
    }

    public function browserAnalysis()
    {
        $ret = array('errno' => 0, 'errmsg' => 'ok', 'data' => array());
        if (isset($_GET['type']))
        {
            switch ($_GET['type'])
            {
                case 'day':
                    $data = self::$_service->basicBrowserInfoByDay($this->startDate, $this->endDate);
                    break;
                default:
                    $data = self::$_service->basicBrowserInfo($this->startDate, $this->endDate);
            }
        }
        else
        {
            $data = self::$_service->basicBrowserInfo($this->startDate, $this->endDate);
        }
        if (is_array($data))
        {
            $ret['data'] = $data;
        } 
        else
        {
            $ret['errmsg'] = $data;
            $ret['errno'] = 1;
         }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return 0;
    }
}