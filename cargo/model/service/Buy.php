<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Buy.php
*	description:	不同的入库操作
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* xxx
*/
class Service_Buy
{
	//拒绝用户某补购请求
	function refuseReplenish ($log_id)
	{
		$info = array(
			'admin_id' => $_SESSION['user_id'], 
			'dealt' => -1,
			'dealt_time' => date('Y-m-d H:i:s')
			);
		$data = new Data_Db();
		$buyLog= $data->getBuyLog(array('id' => $log_id));
		if (empty($buyLog) || $buyLog[0]['dealt']) {
			return '该补购申请不存在或已被处理';
		}
		$data->setBuyLog($info, $log_id);
		return '';
	}


	//管理员处理普通用户的补购申请，并增加该item数量
	function doUserReplenishTask($log_id, $amount, $item_id, $dealt)
	{

		$info = array('admin_id' => $_SESSION['user_id'], 
			'dealt' => $dealt,
			'real_amount' => $amount, 
			'dealt_time' => date('Y-m-d H:i:s')
			);
		$data = new Data_Db();
		$buyLog= $data->getBuyLog(array('id' => $log_id));
		if (empty($buyLog) || $buyLog[0]['dealt']) {
			return '该补购申请不存在或已被处理';
		}
		$item = $data->getItem(array('id' => $item_id));
		if (empty($item) || ($buyLog[0]['item_id'] != 0 && $buyLog[0]['item_id'] != $item_id) ) {
			return '补购信息有误，请确认补购物品合法';
		}

		$info['item_id'] = $item_id;
		if ($dealt == 1) {
			//补购申请被通过
			$takeInfo = array(
				'user_id' => $buyLog[0]['user_id'],
				'item_id' => $item_id,
				'order_num' => $buyLog[0]['order_num'],
				'apply_time' => $buyLog[0]['apply_time'],
				'apply_amount' => $buyLog[0]['apply_amount']
			);
			$data->addItemAmount($info['real_amount'], $_SESSION['user_id'], $item_id);
			if (!$data->makeOrder(array($item_id), array($buyLog[0]['apply_amount']), $_SESSION['user_id'])) {
				$data->addItemAmount(-$info['real_amount'], $_SESSION['user_id'], $item_id);
				unset($info['dealt']);
				$data->setBuyLog($info, $log_id);
				return '补购数量不足以满足申请者需求';
			}
			$data->setTakeLog($takeInfo);
		}
		$data->setBuyLog($info, $log_id);
		return '';
	}

	//管理员主动入库已有物品，在BuyLog表中插入一条记录并增加该item的数量
	function doAdminReplenishTask($item_id, $amount)
	{
		$data = new Data_Db();
		$item = $data->getItem(array('id' => $item_id));
		if (empty($item)) {
			return '补购信息有误，请确认补购物品合法';
		}
		$info = array('item_id' => $item_id, 
			'user_id' => $_SESSION['user_id'], 
			'apply_amount' => $amount, 
			'admin_id' => $_SESSION['user_id'],
			'dealt' => 1, 
			'real_amount' => $amount, 
			'dealt_time' => date('Y-m-d H:i:s')
			);
		$data->setBuyLog($info);
		$data->addItemAmount($amount, $_SESSION['user_id'], $item_id);
		return '';
	}

	function getBuyLogWithUserAndItem()
	{
		$data = new Data_Db();
		$logs = $data->getBuyLog();
		if (empty($logs)) {
			return array();
		}

		$ret = array();
		foreach ($logs as $index => $log) {
			if ($log['apply_time'] == $log['dealt_time'] || !$log['apply_amount']) {
				//管理员主动入库以及申请数量为0的请求不显示
				continue;
			}

			$user = $data->getUser(array('id' => $log['user_id']));
			if ($log['item_id']) {
				$item = $data->getItem(array('id' => $log['item_id']));
				$log['item_name'] = $item[0]['name'];
				$log['item_room'] = $item[0]['room'];
				$log['item_location'] = $item[0]['location'];
				$log['item_type'] = $item[0]['type'];

				$category = $data->getCategory(array('id' => $item[0]['category_id']));
				$log['category_name'] = $category[0]['name'];
			}
			$log['user_name'] = $user[0]['name'];
			$ret[] = $log;
		}
		return $ret;
	}
}