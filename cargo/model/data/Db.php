<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Db.php
*	description:	数据库相关操作
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 		
*/
class Data_Db
{
	private $db;
	function __construct()
	{
		$this->db = Vera_Database::getInstance();
	}

	/**
	 * User信息获取
	 * @param  array  $arr 条件数组
	 * @return array      用户信息
	 */
	function getUser($arr = array()) 
	{
		if (!empty($arr)) {
			$res = $this->db->select('cargo_User', '*', $arr);
		} else {
			$res = $this->db->select('cargo_User', '*');
		}
		return $res;
	}

	/**
	 * 新增或修改user
	 * @param array  $info user信息
	 * @param integer $id   user id
	 * @return int 
	 */
	function setUser ($info, $id = 0)
	{
		if (!$id) {
			$this->db->insert('cargo_User', $info);
			return mysqli_insert_id($this->db->mysql);
		} else {
			return $this->db->update('cargo_User', $info, array('id' => $id));
		}
	}

	/**
	 * Item的获取
	 * @param  array  $arr 筛选条件数组
	 * @return array      物品数据
	 */
	function getItem ($arr = array()) 
	{
		if (!empty($arr)) {
			$res = $this->db->select('cargo_Item', '*', $arr);
		} else {
			$res = $this->db->select('cargo_Item', '*');
		}
		return $res;
	}

	/**
	 * Item数量增加
	 * @param int $amount   数量
	 * @param int $admin_id 管理员id
	 * @param int $item_id 物品id
	 */
	function addItemAmount ($amount, $admin_id, $item_id) 
	{
		$time = date('Y-m-d H:i:s');
		$sql = "UPDATE  cargo_Item SET amount=amount + $amount, update_admin_id=$admin_id, update_time='$time' WHERE (id='$item_id') ";
		return $this->db->query($sql);
	}

	function searchItem ($name) 
	{
		return $this->db->select('cargo_Item', '*', "name like %$name%");
	}

	/**
	 * 新增或修改Item
	 * @param array  $info Item信息
	 * @param integer $id   Item id
	 * @return int 
	 */
	function setItem ($info, $id = 0)
	{
		$info['update_time'] = date('Y-m-d H:i:s');
		if (!$id) {
			$this->db->insert('cargo_Item', $info);
			return mysqli_insert_id($this->db->mysql);
		} else {
			return $this->db->update('cargo_Item', $info, array('id' => $id));
		}
	}

	function deleteItemByCategory($category_id)
	{
		$this->db->update('cargo_Item', array('deleted' => 1), array('category_id' => $category_id));
	}

	/**
	 * Category的获取
	 * @param  array  $arr 筛选条件数组
	 * @return array     	类别数据
	 */
	function getCategory ($arr = array()) 
	{
		if (!empty($arr)) {
			$res = $this->db->select('cargo_Category', '*', $arr);
		} else {
			$res = $this->db->select('cargo_Category', 'id, name');
		}
		return $res;
	}

	/**
	 * 新增或修改Category
	 * @param array  $info Category信息
	 * @param integer $id   Item id
	 * @return int 
	 */
	function setCategory ($info, $id = 0)
	{
		if (!$id) {
			$this->db->insert('cargo_Category', $info);
			return mysqli_insert_id($this->db->mysql);
		} else {
			return $this->db->update('cargo_Category', $info, array('id' => $id));
		}
	}

	/**
	 * 补购记录的获取
	 * @param  array  $arr 筛选条件数组
	 * @return array     	补购记录数据
	 */
	function getBuyLog ($arr = array()) 
	{
		if (!empty($arr)) {
			$res = $this->db->select('cargo_BuyLog', '*', $arr);
		} else {
			$res = $this->db->select('cargo_BuyLog', '*');
		}
		return $res;
	}

	/**
	 * 批量插入新申请，take申请以及buy申请
	 * @param int $user_id       申请者id
	 * @param array $item_ids      物品id数组
	 * @param array $apply_amounts  物品数量数组
	 * @param string $table_name 表名，cargo_BuyLog 或者 cargo_BorrowLog
	 */

	/**
	 * 批量插入新申请，take申请以及buy申请
	 * @param $user_id		int				申请者id
	 * @param $item_ids		array			物品id数组
	 * @param $apply_amounts	array		物品数量数组
	 * @param $order_num		string
	 * @param $table_name		string
	 * @param string $remark	string
	 * @return array|bool|mysqli_result
	 */
	function setBatchLog($user_id, $item_ids, $apply_amounts, $order_num, $table_name , $remark = '')
	{
		$apply_time = date('Y-m-d H:i:s');
		if ($remark) {
			foreach ($item_ids as $index => $item_id) {
				$arr[] = "($user_id, $item_id, $order_num, {$apply_amounts[$index]}, '$apply_time', '$remark')";
			}
			$values = implode(",", $arr);
			$sql = "insert into $table_name(user_id, item_id, order_num, apply_amount, apply_time, remark) values $values ";
		} else {
			foreach ($item_ids as $index => $item_id) {
				$arr[] = "($user_id, $item_id, $order_num, {$apply_amounts[$index]}, '$apply_time')";
			}
			$values = implode(",", $arr);
			$sql = "insert into $table_name(user_id, item_id, order_num, apply_amount, apply_time) values $values ";
		}
		return $this->db->query($sql);
	}

	/**
	 * 领取记录的获取
	 * @param  array  $arr 筛选条件数组
	 * @return array     	领取记录数据
	 */
	function getTakeLog ($arr = array()) 
	{
		if (!empty($arr)) {
			$res = $this->db->select('cargo_BorrowLog', '*', $arr);
		} else {
			$res = $this->db->select('cargo_BorrowLog', '*');
		}
		return $res;
	}

	/**
	 * 新增或修改TakeLog
	 * @param array  $info TakeLog信息
	 * @param integer $id   log id
	 * @return int 
	 */
	function setTakeLog ($info, $id = 0)
	{
		if (!$id) {
			$this->db->insert('cargo_BorrowLog', array_merge($info, array('apply_time' => date('Y-m-d H:i:s'))));
			return mysqli_insert_id($this->db->mysql);
		} else {
			return $this->db->update('cargo_BorrowLog', $info, array('id' => $id));
		}
	}

	/**
	 * 新增或修改BuyLog
	 * @param array  $info BuyLog信息
	 * @param integer $id   log id
	 * @return int 
	 */
	function setBuyLog ($info, $id = 0)
	{
		if (!$id) {
			$this->db->insert('cargo_BuyLog', array_merge($info, array('apply_time' => date('Y-m-d H:i:s'))));
			return mysqli_insert_id($this->db->mysql);
		} else {
			return $this->db->update('cargo_BuyLog', $info, array('id' => $id));
		}
	}

	/**
	 * 管理员审批权限的获取
	 * @param  array  $arr 筛选条件数组
	 * @return array     	权限记录数据
	 */
	function getPrivilege ($arr = array()) 
	{
		if (!empty($arr)) {
			$res = $this->db->select('cargo_AdminPrivilegeLog', '*', $arr);
		} else {
			$res = $this->db->select('cargo_AdminPrivilegeLog', '*');
		}
		return $res;
	}

	/**
	 * 管理员权限新增、删除
	 * @param int $user_id     管理员id
	 * @param int $category_id 类别id
	 */
	function setPrivilege($user_id, $category_id) 
	{
		$time = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `cargo_AdminPrivilegeLog`(user_id, category_id,time) 
				VALUES ($user_id,$category_id,'{$time}') 
				ON DUPLICATE KEY UPDATE  deleted = !deleted, time='{$time}'";
		$this->db->query($sql);
	}

	/**
	 * takeLog，下载
	 * @param  string $start 
	 * @param  string $end   
	 * @return array        
	 */
	function downloadTakeLog($start = null, $end = null, $type = 0) 
	{
		$sql = "select b.dealt_time,i.name as item_name, b.apply_amount, u.name as user_name ,b.real_amount,b.admin_id,b.back_time,b.remark from cargo_BorrowLog b
				left JOIN cargo_Item i on b.item_id=i.id
				LEFT JOIN cargo_Category c on i.category_id=c.id
				LEFT JOIN cargo_User u on b.user_id=u.id
				where b.dealt = 1 AND i.type=$type";
		if ($start != null) {
			$sql .= " AND b.apply_time > $start";
		}
		if ($end != null) {
			$sql .= " AND b.apply_time < $end";
		}

		return $this->db->query($sql);
	}

	/**
	 * @param $item_ids
	 * @param $apply_amounts
	 * @param $user_id
	 * @return bool
	 */
	function minusItemsAmount($item_ids, $apply_amounts, $user_id)
	{
		mysqli_begin_transaction($this->db->mysql, MYSQLI_TRANS_START_READ_WRITE);
		foreach ($item_ids as $index => $id) {
			$item = $this->getItem(array('id' => $id));
			if (empty($item) || $item[0]['amount'] < $apply_amounts[$index]) {
				mysqli_rollback($this->db->mysql);
				return false;
			}
			$this->addItemAmount(-$apply_amounts[$index], $user_id, $id);
		}
		mysqli_commit($this->db->mysql);
		return true;
	}

	/**
	 * @param $item_ids			array
	 * @param $apply_amounts	array
	 * @param $user_id			int
	 * @return bool
	 */
	function makeOrder($item_ids, $apply_amounts, $user_id)
	{
		mysqli_begin_transaction($this->db->mysql, MYSQLI_TRANS_START_READ_WRITE);
		foreach ($item_ids as $index => $id) {
			$item = $this->getItem(array('id' => $id));
			if (empty($item) || $item[0]['amount'] < $apply_amounts[$index]) {
				mysqli_rollback($this->db->mysql);
				return false;
			}
			$this->addItemAmount(-$apply_amounts[$index], $user_id, $id);
		}
		mysqli_commit($this->db->mysql);
		return true;
	}

	function createOrderNum()
	{
		$str = '';
		for ($i = 0; $i < 12; $i ++) {
			$str .= rand(0,9);
		}
		return $str;
	}

	/**
	 * 生成一条订单记录，在cargo_Order表里
	 * @param $orderNum
	 * @param $user_id 			int			申请人id
	 * @param $order_type		string		订单类型,take,borrow,buy三种
	 * @param $remark			string		订单备注
	 */
	function createOrder($orderNum, $user_id, $order_type, $remark)
	{
		$info = array(
			'order_num' 	=> $orderNum,
			'create_time' 	=> date('Y-m-d H:i:s'),
			'user_id' 		=> $user_id,
			'order_type' 	=> $order_type,
			'remark' 		=> $remark
		);
		$this->db->insert('cargo_Order',$info);
	}

	function setBatchTakeLog($item_ids, $apply_amounts, $order_num)
	{
		$arr = array();
		foreach ($item_ids as $index => $item_id) {
			$arr[] = "($order_num, $item_id,  {$apply_amounts[$index]})";
		}
		$values = implode(",", $arr);
		$sql = "insert into cargo_TakeLog (order_num, item_id, apply_amount) values $values ";

		return $this->db->query($sql);
	}

	function setBatchBorrowLog($item_ids, $apply_amounts, $order_num, $borrow_days = array())
	{
		$arr = array();
		if (!empty($borrow_days)) {
			foreach ($item_ids as $index => $item_id) {
				$arr[] = "('$order_num', $item_id,  {$apply_amounts[$index]}, '{$borrow_days[$index]}')";
			}
			$sql = "insert into cargo_BorrowLog (order_num, item_id, apply_amount, borrow_days)";
		} else {
			foreach ($item_ids as $index => $item_id) {
				$arr[] = "('$order_num', $item_id,  {$apply_amounts[$index]}')";
			}
			$sql = "insert into cargo_BorrowLog (order_num, item_id, apply_amount)";
		}

		$values = implode(",", $arr);
		$sql .= " values $values ";

		return $this->db->query($sql);
	}

	/**
	 * @param $item_ids				array
	 * @param $apply_amounts		array
	 * @param $order_num			string
	 * @param $item_names			array
	 * @return array|bool|mysqli_result
	 */
	function setBatchBuyLog($item_ids, $apply_amounts, $order_num, $item_names)
	{
		$arr = array();
		foreach ($item_ids as $index => $item_id) {
			$arr[] = "('$order_num', $item_id,  {$apply_amounts[$index]}, '{$item_names[$index]}')";
		}
		$values = implode(",", $arr);
		$sql = "insert into cargo_BuyLog (order_num, item_id, apply_amount, item_name) values $values ";

		return $this->db->query($sql);
	}

	/**
	 * @param $info array
	 */
	function addBuyLog($info)
	{
		$this->db->insert('cargo_BuyLog', $info);
	}

	/**
	 * @param array $where
	 * @return array|bool|mysqli_result
	 */
	function getOrder($where = array())
	{
		$sql = "SELECT o.*, u.name as u_name FROM cargo_Order o INNER JOIN cargo_User u ON u.id=o.user_id";
		if (!empty($where)) {
			$arr = array();
			foreach($where as $key => $value) {
				$arr[] = "$key = '$value'";
			}
			$sql .= " WHERE " . implode(" AND ", $arr);
		}
		return $this->db->query($sql);
	}

	/**
	 * @param $where	array
	 * @param $table_name	string
	 * @return array|bool|mysqli_result
	 */
	function getOrderDetail($where, $table_name)
	{
		$sql = "SELECT o.*, i.name as i_name,
							i.picUrl as i_picUrl,
							i.amount as i_amount,
							i.room as i_room,
							i.location as i_location,
							i.borrow_days as i_borrow_days,
							i.type as i_type
							from $table_name o LEFT JOIN cargo_Item i ON o.item_id = i.id";
		//用LEFT JOIN是为了适应补购新物品，即item表中无该物品的情况
		$arr = array();
		foreach($where as $key => $value) {
			$arr[] = "o.$key = '$value'";
		}
		$sql .= " WHERE " . implode(" AND ", $arr);
		return $this->db->query($sql);
	}

	/**
	 * @param $where		array
	 * @param $info			array
	 * @param $tableName	string
	 * @return bool|int
	 */
	function modifyOrderDetail($where, $info, $tableName)
	{
		return $this->db->update($tableName, $info, $where);
	}

	/**
	 * @param $where		array
	 * @param $info			array
	 * @return bool|int
	 */
	function modifyOrder($where, $info)
	{
		return $this->db->update("cargo_Order", $info, $where);
	}

	/**
	 * 统计所有物品的类型及其相应数量
	 * @param $itemsIds	array
	 * @return array
	 */
	function getItemsType($itemsIds)
	{
		$type = array('borrow' => 0, 'take' => 0);
		foreach($itemsIds as $item_id) {
			$item_type = $this->db->select('cargo_Item', 'type', array('id' => $item_id));
			if (is_null($item_type)) {
				continue;//物品id非法查不到对应物品的情况
			}
			$key = $item_type ? 'borrow' : 'take';
			$type[$key] ++;
		}
		return $type;
	}

	function getDealtOrder()
	{
		$sql = "SELECT o.*, u.name FROM cargo_Order o INNER JOIN cargo_User u ON u.id=o.user_id";
		$where = " WHERE dealt != 0";
		$sql .= $where;
		return $this->db->query($sql);
	}

	/**
	 * 	借用订单通过之前需检查并设置订单中各物品的归还时间
	 * @param $detail 	array 		getOrderDetail的返回值
	 */
	function checkAndSetBackTime($detail)
	{
		foreach($detail as $row) {
			if (!$row['back_time']) {
				//未设置归还时间时订单通过前需设置该时间
				if (!$row['borrow_days']) {
					$borrow_day = $row['i_borrow_day'];//申请者未填写申请天数的情况，使用设置的item的borrow_days作为借用天数
				} else {
					$borrow_day = $row['borrow_days'];//申请者填写了borrow_days
				}
				$this->db->update("cargo_BorrowLog", array('back_time' => date('Y-m-d', strtotime("+ {$borrow_day} day"))), array('id' => $row['id']));
			}
		}
	}

	/**
	 * 获取贵重物品列表，并在user表中对应上管理员名字
	 * @return array|bool|mysqli_result
	 */
	function getValuablesList()
	{
		$sql = "SELECT v.*, u.name as admin_name from cargo_Valuables v LEFT JOIN cargo_User u ON v.update_admin = u.id where v.deleted = 0";
		return $this->db->query($sql);
	}

	function getValuables($where = array())
	{
		return $this->db->select('cargo_Valuables', '*', $where);
	}

	function modifyValuables($info, $id = null)
	{
		if (empty($id)) {
			$this->db->insert('cargo_Valuables', $info);
			return mysqli_insert_id($this->db->mysql);
		} else {
			return $this->db->update('cargo_Valuables', $info, array('id' => $id));
		}
	}

	/**
	 *多表联合查询借用订单详情及其归还信息
	 * @param $orderNum		string		借用类型订单号
	 * @return array|bool|mysqli_result
	 */
	function getDetailWithBackInfo($orderNum)
	{
		$sql = "select
				i.name as i_name,
				borrow.real_amount as borrow_amount,
				borrow.back_time as borrow_back_time,
				u.name as back_admin_name,
				borrow.item_id as item_id,
				back.back_amount as back_back_amount,
				back.time as back_back_time
				from cargo_BorrowLog borrow
				LEFT JOIN cargo_BackLog back ON borrow.order_num=back.order_num AND borrow.item_id = back.item_id
				LEFT JOIN cargo_Item i on i.id=borrow.item_id
				left join cargo_User u on u.id=back.admin_id
				where borrow.order_num = $orderNum
				";
		return $this->db->query($sql);
	}

	function getBackInfo($where = array())
	{
		return $this->db->select('cargo_BackLog', array('item_id', 'time as back_time', 'back_amount'), $where);
	}

	/**
	 * @param $info		array		要插入的信息
	 */
	function setBackLog($info)
	{
		$this->db->insert('cargo_BackLog', $info);
	}
}