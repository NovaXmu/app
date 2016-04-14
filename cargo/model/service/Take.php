<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Take.php
*	description:	Take日志数据组合
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 	xx
*/
class Service_Take
{
	
	function getTakeLogWithUserAndItem()
	{
		$data = new Data_Db();
		$logs = $data->getTakeLog();
		if (empty($logs)) {
			return array();
		}
		$ret = array();

		$allPrivileges = $data->getPrivilege(array('user_id' => $_SESSION['user_id'], 'deleted' => 0));
		$allAvailableCategory = array_column($allPrivileges, 'category_id');

		foreach ($logs as $index => $log) {
			$user = $data->getUser(array('id' => $log['user_id']));
			$item = $data->getItem(array('id' => $log['item_id']));
			$log['item_name'] = $item[0]['name'];
			$log['item_room'] = $item[0]['room'];
			$log['item_location'] = $item[0]['location'];
			$log['item_type'] = $item[0]['type'];
			$log['user_name'] = $user[0]['name'];

			$category = $data->getCategory(array('id' => $item[0]['category_id']));
			if (!in_array($category[0]['id'], $allAvailableCategory)) {
				continue;
			}
			$log['category_name'] = $category[0]['name'];
			$ret[] = $log;
		}
		return $ret;
	}

	//管理员审批申请
	function doTakeTask($log_id, $info) 
	{
		$data = new Data_Db();
		$takeLog = $data->getTakeLog(array('id' => $log_id, 'dealt' => 0));
		if (empty($takeLog)) {
			return '该请求不存在或已被审批';
		}

		$item = $data->getItem(array('id' => $takeLog[0]['item_id']));
		if (empty($item)) {
			return '被审批物品非法';
		}

		$privilege = $data->getPrivilege(array('user_id' => $_SESSION['user_id'], 'category_id' => $item[0]['category_id']));
		if (empty($privilege)) {
			return '无审批该物品权限';
		}

		if ($info['dealt'] == 1) {
			if (!$data->makeOrder(array($item[0]['id']), array($info['real_amount'] - $takeLog[0]['apply_amount']),  $_SESSION['user_id'])) {
				return '库存不足';
			}
		} else {
			$data->addItemAmount($takeLog[0]['apply_amount'], $_SESSION['user_id'], $item[0]['id']); //拒绝时数量加
		}
		$info = array_merge($info, array('admin_id' => $_SESSION['user_id'], 'dealt_time' => date('Y-m-d H:i:s')));
		$data->setTakeLog($info, $log_id);
		return '';
	}
}