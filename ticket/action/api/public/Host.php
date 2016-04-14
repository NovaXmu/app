<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/19
 * Time: 21:35
 * File: Host.php
 * Description: 申请一个活动，以及活动票据兑换
 */

class Action_Api_Public_Host
{
    function __construct()
    {
        if (empty($_SESSION['host_telephone'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => "非法请求"), JSON_UNESCAPED_UNICODE);
            exit;//临时做这样的处理
        }
    }

    function run ()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : null;
        switch($m) {
            case 'exchange':
                $this->exchange();
                break;
            case 'assignSeat':
                $this->assignSeat();
                break;
            default:
                echo json_encode(array('errno' => 1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }
    }


    function assignSeat()
    {
        if (!isset($_POST['logId']) || !isset($_POST['seat'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return false;
        }

        $service = new Service_Host();
        $msg = $service->assignSeat($_POST['logId'], $_POST['seat']);
        if ($msg) {
            echo json_encode(array('errno' => 1, 'errmsg' => $msg), JSON_UNESCAPED_UNICODE);
            return false;
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return true;
    }

    function exchange()
    {
        if (!isset($_POST['actID']) || !isset($_POST['token'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数不对'), JSON_UNESCAPED_UNICODE);
            return false;
        }

        $service = new Service_Host();
        $ret = array('errno' => 0, 'errmsg' => 'ok');
        try {
            $info = $service->exchange($_POST['actID'], $_POST['token']);
            $ret['data'] = $info;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $ret['errno'] = 1;
            $ret['errmsg'] = $msg;
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }


}