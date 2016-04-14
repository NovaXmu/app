<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Item.php
*	description:	Item 
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Service_Item 
{
	
	function getAllItemsWithCategory()
	{
		$data = new Data_Db();
		$res = $data->getItem(array('deleted' => 0));

		if (empty($res)) {
			return array();
		}

		foreach ($res as $key => $row) {
			$ret[$row['category_id']][] = $row;
		}
		return $ret;
	}

	function checkFile() 
	{
		if ($_FILES['file']['error'] > 0){
			return '上传出错';
		}
		
		$type = explode('.', $_FILES['file']['name']);
		$type = end($type);
		$allowedExts = array('png','jpeg','jpg','gif','text','doc','docs','excel','pdf');
		if (!in_array($type, $allowedExts)){
			return '格式不对';
		}
		if ($_FILES['file']['size'] > 500000 || !$_FILES['file']['size']){
			return '文件大小需在0-500KB之间';
		}
		
		return '';
	}

	function saveFile($itemName, $itemId, $table)
	{
		$data = new Data_Db();
		$name = md5($itemName . time());
		$type = explode('.', $_FILES['file']['name']);
		$type = end($type);
		$dir = SERVER_ROOT . "static/cargo/$table";
		if (!is_dir($dir)){
			mkdir($dir);
		}
		if (file_exists($dir ."/" . $name . '.' . $type)){
			unlink($dir ."/" . $name . '.' . $type);//允许覆盖之前的图片
		}
		$res = move_uploaded_file($_FILES['file']['tmp_name'], $dir ."/" . $name . '.' . $type);
		if (!$res){
			return '保存文件错误';
		}
		switch($table) {
			case 'item':
				$data->setItem(array('picUrl' => "/static/cargo/{$table}/" . $name . '.' . $type), $itemId);
				break;
			case 'valuables':
				$data->modifyValuables(array('picUrl' => "/static/cargo/{$table}/" . $name . '.' . $type), $itemId);
				break;
			default:
				return '非法类型';
		}
		return '';
	}
}