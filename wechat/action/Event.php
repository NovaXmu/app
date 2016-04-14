<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Event.php
*    description:    事件信息入口
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
* 事件信息处理入口
*/
class Action_Event extends Action_Base
{

    function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function run()
    {
        Vera_Log::addLog('test',date('Y-m-d H:i:s').'|'.'Event.php');
        $resource = $this->getResource();
        Vera_Log::addNotice('event', $resource['Event']);

        switch ($resource['Event']) {
            case 'CLICK':
                $ret = $this->_click();
                break;
            case 'scancode_waitmsg': // 扫码推事件且弹出“消息接收中”提示框的事件推送
                $ret = $this->_scancode();
                break;
            case 'subscribe':
                $ret = $this->_subscribe();
                break;
            case 'unsubscribe':
                echo '';
                return true;
            default:
                echo '';
                return true;
                $conf = Vera_Conf::getAppConf('common');
                $ret = $conf['defaultReply'];
                break;
        }

        if (empty($ret)) {
            throw new Exception("很抱歉公众号出现异常", 1);
        }

        //寻找模板
        $view = new View_Wechat($resource);
        $view->assign($ret);
        $view->display();

        return true;

    }

    private function _click()
    {
        $resource = $this->getResource();
        Vera_Log::addNotice('eventKey', $resource['EventKey']);

        $conf = Vera_Conf::getAppConf('common');
        $result = Data_Wechat_Db::eventReply($resource['EventKey']);

        if (!$result) {
            //出错回复
            $ret = $conf['errMsg'];
        }
        elseif (in_array($result['replyType'], $conf['replyType'])) {
            //固定回复
            $ret['type'] = $result['replyType'];
            $ret['data'] = json_decode($result['reply'], true);
        }
        else {
            //功能性回复
            $class = 'Service_' . $result['replyType'];
            $instance = new $class($resource);
            $ret = $instance->{$result['reply']}();
        }

        return $ret;
    }

    private function _scancode()
    {
        $resource = $this->getResource();
        Vera_Log::addNotice('eventKey', $resource['EventKey']);
        Vera_Log::addNotice('scanResult', $resource['ScanCodeInfo']->ScanResult);

        $result = $resource['ScanCodeInfo']->ScanResult;//二维码扫出的信息
        $result = explode('&',$result); //二维码规范，arg&arg...
        if (count($result) < 2) {
            throw new Exception('请更新客户端或扫描签到指定二维码', 1);
            return false;
        }

        $act = $result[0];
        $token = $result[1];
        Vera_Log::addNotice('act', $act);
        $service = new Service_Rollcall($resource);
        if (strlen($act) < 32) {// @temp: 网络文化节所需扫码发网薪功能
            $ret = $service->pay($act, $token);
        } else {
            $ret = $service->checkin($act, $token);
        }
        return $ret;
    }

    private function _subscribe()
    {
        $resource = $this->getResource();
        Vera_Log::addNotice('event', $resource['Event']);

        $conf = Vera_Conf::getAppConf('common');
        return $conf['subscribe'];//回复欢迎信息
    }
}

?>
