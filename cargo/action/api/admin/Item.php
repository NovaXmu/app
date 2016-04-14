<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Item.php
*	description:	Item.php, 管理员针对物品的操作
*
*	@author Nili
*	@license Apache v2 License
*
**/

class Action_Api_Admin_Item
{

    function run ()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : 'add';
        switch ($m) {
            case 'add':
                $msg = $this->addItem();
                break;
            case 'pic':
                $msg = $this->uploadPic();
                break;
            case 'picFromWechat':
                $msg = $this->uploadPicFromWechat();
                break;
            case 'modify':
                $msg = $this->modifyItem();
                break;
            case 'addAmount':
                $msg = $this->addAmount();
                break;
            case 'getValuablesList':
                $msg = $this->getValuablesList();
                break;
            case 'modifyValuables':
                $msg = $this->modifyValuables();
                break;
            default:
                $msg = 'm非法';
        }
        if ($msg) {
            echo json_encode(array('errno' => 1, 'errmsg' => $msg), JSON_UNESCAPED_UNICODE);
        }
    }

    function addAmount()
    {
        if (!isset($_POST['item_id']) || !is_numeric($_POST['item_id']) || !isset($_POST['amount']) || !is_numeric($_POST['amount'])) {
            return '参数非法';
        }
        $data = new Data_Db();
        $item = $data->getItem(array('id' => $_POST['item_id']));
        if (empty($item)) {
            return '非法物品id';
        }
        if (!$data->minusItemsAmount(array($_POST['item_id']), array(-$_POST['amount']), $_SESSION['user_id'])) {
            return '入库失败';
        }
        $info = array(
            'item_id' => $_POST['item_id'],
            'item_name' => $item[0]['name'],
            'order_num' => 0,
            'apply_amount' => $_POST['amount'],
            'admin_id' => $_SESSION['user_id'],
            'real_amount' => $_POST['amount'],
            'dealt_time' => date('Y-m-d H:i:s')
        );
        $data->addBuyLog($info);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function addItem()
    {
        $keys = array('name', 'room', 'location', 'category_id');
        foreach ($keys as $key) {
            if (!isset($_POST[$key]) || empty($_POST[$key])) {
                return '参数有误';
            }
            $info[$key] = $_POST[$key];
        }
        $info['type'] = (isset($_POST['type'])) ? $_POST['type'] : 0;
        $info['amount'] = is_numeric($_POST['amount']) ? $_POST['amount'] : 0;
        $data = new Data_Db();
        $category = $data->getCategory(array('id' => $_POST['category_id']));
        if (empty($category)) {
            return '所属分类不存在';
        }
        if ($category[0]['type'] != $_POST['type']) {
            return '物品类别与所选分类类别不一致';
        }
        if ($_POST['type'] == 1 && (!isset($_POST['borrow_days']) || !is_numeric($_POST['borrow_days'])) ) {
            return '借用类型物品归还时间设置不当';
        } else if ($_POST['type'] == 1 && is_numeric($_POST['borrow_days'])) {
            $info['borrow_days'] = intval($_POST['borrow_days']);
        }
        $info['type'] = $category[0]['type'];
        if (isset($_POST['author']) && !empty($_POST['author']))
        {
            $info['name'] .= '/作者：' . $_POST['author'];
        }
        $info['update_admin_id'] = $_SESSION['user_id'];
        if (!$item_id = $data->setItem($info)) {
            return '未知原因新增失败';
        }

        $info = array('item_id' => $item_id,
            'user_id' => $_SESSION['user_id'],
            'apply_amount' => $info['amount'],
            'admin_id' => $_SESSION['user_id'],
            'dealt' => 1,
            'real_amount' => $info['amount'],
            'dealt_time' => date('Y-m-d H:i:s')
        );
        $data->setBuyLog($info);
        Vera_Log::addVisitLog('res', 'ok');
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $item_id ), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function uploadPic()
    {
        if (!isset($_POST['item_id']) || !is_numeric($_POST['item_id'])) {
            return '参数有误';
        }

        $data = new Data_Db();
        $table = isset($_POST['type']) ? $_POST['type'] : 'item';//
        switch($table) {
            case 'item':
                $table = 'item';
                $item = $data->getItem(array('id' => $_POST['item_id']));
                break;
            case 'valuables':
                $table = 'valuables';
                $item = $data->getValuables(array('id' => $_POST['item_id']));
                break;
            default:
                return '非法type';
        }
        if (empty($item)) {
            return '物品不存在';
        }

        $service = new Service_Item();
        if (!$msg = $service->checkFile()) {
            if (!$msg = $service->saveFile($item[0]['name'], $item[0]['id'], $table)) {
                echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
                return '';
            }
        }
        return $msg;
    }

    function uploadPicFromWechat()
    {
        if (!isset($_POST['media_id']) || empty($_POST['media_id'])
            || !isset($_POST['item_id']) || !is_numeric($_POST['item_id'])) {
            return '参数有误';
        }

        $data = new Data_Db();
        $table = isset($_POST['type']) ? $_POST['type'] : 'item';//

        $wechat = new Data_Wechat();
        $access_token = $wechat->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id={$_POST['media_id']}";
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => 0,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $img = curl_exec($ch);
        $dir = SERVER_ROOT . "static/cargo/$table";
        $info = curl_getinfo($ch);
        $type = explode('/', $info['content_type'])[1];
        if ($info['size_download'] > 500 * 1024) {
            return '图片大于500KB';
        }

        $time = time();
        switch($table) {
            case 'item':
                $item = $data->getItem(array('id' => $_POST['item_id']));
                if (empty($item)) {
                    return '物品id非法';
                }
                $data->setItem(array('picUrl' => '/static/cargo/item/' . md5($item[0]['name'] . $time) . '.' . $type), $item[0]['id']);
                break;
            case 'valuables':
                $item = $data->getValuables(array('id' => $_POST['item_id']));
                if (empty($item)) {
                    return '物品id非法';
                }
                $data->modifyValuables(array('picUrl' => '/static/cargo/valuables/' . md5($item[0]['name'] . $time) . '.' . $type), $item[0]['id']);
                break;
            default:
                return '非法type';
        }

        $filename = "$dir/" . md5($item[0]['name'] . $time) . ".$type";
        file_put_contents($filename, $img);

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function modifyItem()
    {
        $keys = array('name', 'room', 'location', 'category_id');
        if (!isset($_POST['item_id']) || !is_numeric($_POST['item_id'])) {
            return "参数非法";
        }
        $info = array();
        foreach($_POST as $key => $value) {
            if (in_array($key, $keys)) {
                $info[$key] = $value;
            }
        }

        if(isset($_POST['deleted']) && $_POST['deleted'] == 1) {
            $info['deleted'] = 1;
        }
        $data = new Data_Db();
        $item = $data->getItem(array('id' => $_POST['item_id'], 'deleted' => 0));
        if (empty($item)) {
            return '被修改物品不存在或已被删除';
        }
        if (isset($info['category_id'])) {
            $category = $data->getCategory(array('id' => $info['category_id'], 'deleted' => 0));
            if (empty($category) || $category[0]['type'] != $item[0]['type']) {
                return "被修改物品分类信息非法";
            }
        }

        $data->setItem($info, $_POST['item_id']);
        echo json_encode(array('errno' => 0, 'errmsg' => "ok"), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function getValuablesList()
    {
        $data = new Data_Db();
        $list = $data->getValuablesList();
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $list), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function modifyValuables()
    {
        $keys = array('name', 'amount', 'remark', 'price', 'deleted', 'scale');
        $info = array();
        foreach($keys as $key) {
            if (isset($_POST[$key]) && !empty($_POST[$key])) {
                $info[$key] = $_POST[$key];
            }
        }
        if (empty($info)) {
            return '参数非法';
        }

        $data = new Data_Db();
        if((!isset($_POST['id']) || !is_numeric($_POST['id'])) && (!isset($_POST['name']) || empty($_POST['name']))) {
            return '新增时物品名称不能为空';
        }
        $res = $data->modifyValuables($info, isset($_POST['id']) ? $_POST['id'] : null);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $res), JSON_UNESCAPED_UNICODE);
        return '';
    }
}