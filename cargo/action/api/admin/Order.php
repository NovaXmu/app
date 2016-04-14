<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Order.php
*	description:	管理员订单管理相关
*
*	@author Nili
*	@license Apache v2 License
*
**/

class Action_Api_Admin_Order
{
    function run()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : '';
        switch ($m) {
            case 'getList' :
                $this->getOrderList();
                break;
            case 'getOrderDetail':
                $this->getOrderDetail();
                break;
            case 'modify':
                $this->modifyOrderDetail();
                break;
            case 'modifyBuyLog':
                $this->modifyBuyLog();
                break;
            case 'deal':
                $this->dealOrder();
                break;
            case 'getToBeReturnOrderList':
                $this->getToBeReturnOrderList();
                break;
            case 'returnItem':
                $this->returnItem();
                break;
            default:
                echo json_encode(array('errno' => 1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }
    }

    function getOrderList()
    {
        $service = new Service_Order();
        $allType = array('borrow', 'buy', 'take');
        if (isset($_GET['type']) && !empty($_GET['type']) && in_array($_GET['type'], $allType)) {
            $data = $service->getOrderListByType($_GET['type']);
        } else if (isset($_GET['type']) && $_GET['type'] == "dealt") {
            $db = new Data_Db();
            $data = $db->getDealtOrder();
        } else {
            $data = $service->getOrderList();
        }

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $data), JSON_UNESCAPED_UNICODE);
        return;
    }

    function getOrderDetail()
    {
        if (!isset($_GET['orderNum']) || !is_numeric($_GET['orderNum'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '订单号非法'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $orderNum = $_GET['orderNum'];
        $service = new Service_Order();
        $detail = $service->getOrderDetail($orderNum);
        if (is_string($detail)) {
            echo json_encode(array('errno' => 1, 'errmsg' => $detail), JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $detail), JSON_UNESCAPED_UNICODE);
        return;
    }

    function modifyOrderDetail()
    {
        //管理员修改订单只能修改物品数量，以及借用物品的归还时间,数量的修改涉及到对应物品数量的变动
        if (!isset($_POST['orderNum']) || !is_numeric($_POST['orderNum']) || !isset($_POST['itemId']) || !is_numeric($_POST['itemId'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '订单号非法或物品id非法'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $orderNum = $_POST['orderNum'];
        $itemId = $_POST['itemId'];

        if (isset($_POST['back_time']) && !empty($_POST['back_time'])) {
            $backTime = strtotime($_POST['back_time']);
            if ($backTime < time()) {
                echo json_encode(array('errno' => 1, 'errmsg' => '归还时间设置时间不准确'), JSON_UNESCAPED_UNICODE);
                return;
            }
            $info['back_time'] = date('Y-m-d H:i:s', $backTime);
        }
        if (isset($_POST['realAmount']) && is_numeric($_POST['realAmount'])) {
            $info['real_amount'] = $_POST['realAmount'];
        }
        if (empty($info)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '修改信息有误'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $service = new Service_Order();
        $msg = $service->modifyOrderDetail($orderNum, $itemId, $info);
        if (!empty($msg)) {
            echo json_encode(array('errno' => 1, 'errmsg' => $msg), JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
    }

    function modifyBuyLog()
    {
        if (!isset($_POST['log_id']) || !is_numeric($_POST['log_id']) || !isset($_POST['item_id']) || !is_numeric($_POST['item_id'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数不对'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $data = new Data_Db();
        $logInfo = $data->getOrderDetail(array('id' => $_POST['log_id']), 'cargo_BuyLog');
        $item = $data->getItem(array('id' => $_POST['item_id']));
        if (empty($logInfo) || empty($item) || $logInfo[0]['item_id'] > 0) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $data->modifyOrderDetail(array('id' => $_POST['log_id']), array('item_id' => $_POST['item_id']), 'cargo_BuyLog');
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
    }

    function dealOrder()
    {
        if (!isset($_POST['orderNum']) || !is_numeric($_POST['orderNum']) || !isset($_POST['deal'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => "参数有误"), JSON_UNESCAPED_UNICODE);
            return;
        }
        $orderNum = $_POST['orderNum'];
        $dealt = ($_POST['deal'] == 1) ? 1 : -1;

        $service = new Service_Order();
        $msg = $service->dealOrder($orderNum, $dealt);
        if ($msg) {
            echo json_encode(array('errno' => 1, 'errmsg' => $msg), JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(array('errno' => 0, 'errmsg' => "ok"), JSON_UNESCAPED_UNICODE);
    }

    function getToBeReturnOrderList()
    {
        if (!isset($_POST['order_num']) || empty($_POST['order_num'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $data = new Data_Db();
        $detail = $data->getBackInfo(array('order_num' => $_POST['order_num']));
        $res = array();
        if (!empty($detail)) {
            foreach($detail as $row) {
                $res[$row['item_id']] = $row;
            }
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' =>$res), JSON_UNESCAPED_UNICODE);
    }

    function returnItem()
    {
        $params = array('item_id', 'order_num', 'back_amount');
        foreach ($params as $param) {
            if (!isset($_POST[$param]) || empty($_POST[$param])) {
                echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $data = new Data_Db();

        if(!empty($data->getBackInfo(array('order_num' => $_POST['order_num'], 'item_id' => $_POST['item_id'])))) {
            echo json_encode(array('errno' => 1, 'errmsg' => '该物品已被归还，请勿重复操作'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $info = array(
            'item_id' => $_POST['item_id'],
            'order_num' => $_POST['order_num'],
            'back_amount' => $_POST['back_amount'],
            'admin_id' => $_SESSION['user_id'],
            'time' => date('Y-m-d H:i:s')
        );
        if (!$data->minusItemsAmount(array($_POST['item_id']), array(-$_POST['back_amount']), $_SESSION['user_id'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '归还失败，请确认信息无误后重新归还'));
            return;
        }
        $data->setBackLog($info);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'));
    }
}
