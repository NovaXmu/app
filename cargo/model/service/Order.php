<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Order.php
*	description:	订单相关操作service层
*
*	@author Nili
*	@license Apache v2 License
*
**/

class Service_Order
{
    public $data;
    function __construct()
    {
        $this->data = new Data_Db();
    }

    function getOrderListByType($type)
    {
        return $this->data->getOrder(array('order_type' => $type));
    }

    function getOrderList()
    {
        $res = $this->data->getOrder();
        $arr = array();
        foreach($res as $row) {
            $arr[$row['order_type']][] = $row;
        }
        return $arr;
    }

    function getOrderDetail($orderNum)
    {
        $order = $this->data->getOrder(array('order_num' => $orderNum));
        if (empty($order)) {
            return '订单不存在';
        }

        $tableName = "cargo_" . ucfirst($order[0]['order_type']) . "Log"; //数据库中BorrowLog、TakeLog、BuyLog表分别对应了Order表中的三种order_type
        $detail = $this->data->getOrderDetail(array('order_num' => $orderNum), $tableName);

        if ($order[0]['order_type'] == 'borrow' && !empty($detail)) {
            //借用类型订单需处理归还时间问题
            foreach ($detail as $index => $row) {
                if (empty($row['back_time']) && !empty($row['borrow_days'])) {
                    $detail[$index]['back_time'] = date('Y-m-d', strtotime(" + {$row['borrow_days']} day"));
                }
            }
        }
        return $detail;
    }

    function modifyOrderDetail($orderNum, $itemId, $info)
    {
        $data = new Data_Db();
        $order = $data->getOrder(array('order_num' => $orderNum, 'dealt' => 0));
        if (empty($order)) {
            return "订单不存在或已被处理";
        }
        $tableName = "cargo_" . ucfirst($order[0]['order_type']) . "Log";

        if (isset($info['real_amount'])) {
            $detail = $data->getOrderDetail(array('order_num' => $orderNum, 'item_id' => $itemId), $tableName);

            //数量的变动，修改订单时多次修改实际数量以及首次修改实际数量
            if ($detail[0]['real_amount']) {
                $minusAmount = $info['real_amount'] - $detail[0]['real_amount'];
            } else {
                $minusAmount = $info['real_amount'] - $detail[0]['apply_amount'];
            }

            if($order[0]['order_type'] == 'buy') {
                //补购订单的物品数量变化与领取跟借用不同
                if ($detail[0]['real_amount']) {
                    $minusAmount = $detail[0]['real_amount'] - $info['real_amount'];
                } else {
                    $minusAmount = -$info['real_amount']; //首次补购录入数量，物品数量变动为增加$info['real_amount']个
                }
            }

            if (!$data->minusItemsAmount(array($itemId), array($minusAmount), $_SESSION['user_id'])) {
                return "物品库存不足";
            }
        }
        if ($order[0]['order_type'] !== "borrow" && isset($info['back_time'])) {
            unset($info['back_time']);
        }

        $data->modifyOrderDetail(array('order_num' => $orderNum, 'item_id' => $itemId), $info, $tableName);
        return "";
    }

    function dealOrder($orderNum, $dealt)
    {
        $order = $this->data->getOrder(array('order_num' => $orderNum, 'dealt' => 0));
        if (empty($order)) {
            return '订单不存在或已被处理';
        }

        switch($order[0]['order_type']) {
            case 'buy':
                if ($dealt == 1) {
                    return $this->passBuyOrder($order[0]);
                }   //另一种拒绝补购订单的情况处理方式在下面，此处故意无break
            case 'take':
            case 'borrow':
                if ($dealt == 1 || $order[0]['order_type'] == 'buy') {
                    //借用领取通过以及补购不通过时，订单状态的变化不会引起物品数量变化
                    if (!$this->data->modifyOrder(array('order_num' => $orderNum, 'dealt' => 0), array(
                        'dealt' => $dealt,
                        'deal_time' => date('Y-m-d H:i:s'),
                        'admin_id' => $_SESSION['user_id']))) {
                        return '订单不存在或已被处理';
                    }
                    break;
                } else {
                    return $this->refuseGeneralOrder($order[0]);
                }
        }
        return '';
    }

    /**
     * 拒绝领取或借用类型订单时，订单内物品数量应有变化，物品们数量变化&标记订单已被拒绝&为了省事就不操作对应Log表了
     * @param $order        array
     * @return string
     */
    function refuseGeneralOrder($order)
    {
        $tableName = "cargo_" . ucfirst($order['order_type']) . "Log";
        $detail = $this->data->getOrderDetail(array('order_num' =>$order['order_num']), $tableName);
        if (empty($detail)) {
            return '订单信息有误';
        }

        $itemIds = array_column($detail, 'item_id');
        $applyAmounts = array_column($detail, 'apply_amount');
        $realAmounts = array_column($detail, 'real_amount');

        $minusAmounts = array();
        foreach($applyAmounts as $index => $amount) {
            if ($realAmounts[$index]) {
                $minusAmounts[$index] = - $realAmounts[$index]; //拒绝订单要把之前减少的数量增加回来
                continue;
            }
            $minusAmounts[$index] = - $applyAmounts[$index];
        }

        if (!$this->data->minusItemsAmount($itemIds, $minusAmounts, $_SESSION['user_id'])) {
            return '订单内物品数量处理异常';
        }
        $this->data->modifyOrder(array('order_num' => $order['order_num']), array(
            'dealt' => -1,
            'deal_time' => date('Y-m-d H:i:s'),
            'admin_id' => $_SESSION['user_id']));
        return '';
    }

    /**
     * 补购订单通过处理，检查是否满足通过条件，置原订单已通过&生成新订单&对应log插入操作
     * @param $order        array
     * @return string
     */
    function passBuyOrder($order)
    {
        $detail = $this->data->getOrderDetail(array('order_num' =>$order['order_num']), "cargo_BuyLog");
        if (empty($detail)) {
            return '订单信息有误';
        }

        $itemIds = array_column($detail, 'item_id');
        foreach($itemIds as $itemId) {
            if ($itemId < 1) {
                return '订单中还有新物品未添加，请添加后再通过';
            }
        }
        $applyAmounts = array_column($detail, 'apply_amount');
        $itemsType = $this->data->getItemsType($itemIds);
        $tableName = '';
        foreach($itemsType as $key => $value) {
            if ($value = count($itemIds)) {
                $tableName = "cargo_" . ucfirst($key) . "Log";
                break;
            }
        }
        if (!$tableName) {
            return "订单物品类型不统一,无法通过";
        }


        $newOrderNum = $this->data->createOrderNum();
        if ($this->data->minusItemsAmount($itemIds, $applyAmounts, $order['user_id'])) {
            if ($key == "borrow") {
                $this->data->setBatchBorrowLog($itemIds, $applyAmounts, $newOrderNum);
//                $this->data->checkAndSetBackTime($detail);
            } else if ($key == "take") {
                $this->data->setBatchTakeLog($itemIds, $applyAmounts, $newOrderNum);
            } else {
                return "类型不为借用或领取任一种"; //此处是以防以后其他类型订单
            }
            $this->data->createOrder($newOrderNum, $order['user_id'], $key, $order['remark']);
            $this->data->modifyOrder(array('order_num' => $order['order_num']),array(
                'dealt' => 1,
                'deal_time' => date('Y-m-d H:i:s'),
                'admin_id' => $_SESSION['user_id']));
            return '';
        }
        return '库存不足，订单处理失败';
    }

}