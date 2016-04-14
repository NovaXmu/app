<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Order.php
 *	description:	普通用户下订单
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/

/**
 *
 */
class Action_Api_User_Order
{

    function run()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : '';
        switch($m) {
            case 'take' :
                $this->_makeTakeOrder();
                break;
            case 'borrow':
                $this->_makeBorrowOrder();
                break;
            case 'buy':
                $this->_makeBuyOrder();
                break;
            default:
                echo json_encode(array('errno' => 1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }

    }

    private function _makeBuyOrder()
    {
        $params = array('itemIds', 'itemNames' ,'applyAmounts', 'remark');
        foreach ($params as $key) {
            if (!isset($_POST[$key]) || empty($_POST[$key])) {
                Vera_Log::addVisitLog('res', '参数有误');
                echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $remark = $_POST['remark'];
        $itemIds = json_decode($_POST['itemIds'], true);
        $applyAmounts = json_decode($_POST['applyAmounts'], true);
        $itemNames = json_decode($_POST['itemNames'], true);
        if (!is_array($itemIds) || !is_array($applyAmounts) || !is_array($itemNames) || count($itemIds) != count($applyAmounts) || count($itemIds) != count($itemNames)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = new Data_Db();
        $orderNum = $data->createOrderNum();

        $data->createOrder($orderNum, $_SESSION['user_id'], 'buy', $remark);
        $data->setBatchBuyLog($itemIds, $applyAmounts, $orderNum, $itemNames);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return;
    }

    private function _makeBorrowOrder()
    {
        $params = array('itemIds','applyAmounts', 'remark', 'borrowDays');
        foreach ($params as $key) {
            if (!isset($_POST[$key]) || empty($_POST[$key])) {
                echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $remark = $_POST['remark'];
        $itemIds = json_decode($_POST['itemIds'], true);
        $applyAmounts = json_decode($_POST['applyAmounts'], true);
        $borrowDays = json_decode($_POST['borrowDays'], true);
        if (!is_array($itemIds) || !is_array($applyAmounts) || !is_array($borrowDays) || count($itemIds) != count($applyAmounts) || count($itemIds) != count($borrowDays)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        foreach($borrowDays as $index => $borrowDay) {
            $borrowDays[$index] = intval($borrowDay); //此处数据必须保证为整数，否则会影响到归还时间正确性
        }
        $data = new Data_Db();

        $itemsType = $data->getItemsType($itemIds);
        if ($itemsType['borrow'] != count($itemIds)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '订单中包含领取类型物品，下单失败'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $orderNum = $data->createOrderNum();
        if (!$data->minusItemsAmount($itemIds, $applyAmounts, $_SESSION['user_id'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '库存不足'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $data->createOrder($orderNum, $_SESSION['user_id'], 'borrow', $remark);
        $data->setBatchBorrowLog($itemIds, $applyAmounts, $orderNum, $borrowDays);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return;
    }

    private function _makeTakeOrder()
    {
        $params = array('itemIds','applyAmounts', 'remark');
        foreach ($params as $key) {
            if (!isset($_POST[$key]) || empty($_POST[$key])) {
                echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $remark = $_POST['remark'];
        $itemIds = json_decode($_POST['itemIds'], true);
        $applyAmounts = json_decode($_POST['applyAmounts'], true);
        if (!is_array($itemIds) || !is_array($applyAmounts) || count($itemIds) != count($applyAmounts)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = new Data_Db();

        $itemsType = $data->getItemsType($itemIds);
        if ($itemsType['borrow'] != count($itemIds)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '订单中包含借用类型物品，下单失败'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $orderNum = $data->createOrderNum();
        if (!$data->minusItemsAmount($itemIds, $applyAmounts, $_SESSION['user_id'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '库存不足'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $data->createOrder($orderNum, $_SESSION['user_id'], 'take', $remark);
        $data->setBatchTakeLog($itemIds, $applyAmounts, $orderNum);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return;
    }

}