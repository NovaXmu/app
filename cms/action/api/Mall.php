<?php
/**
*
*    @copyright  Copyright (c) 2015 Nili
*    All rights reserved
*
*    file:            Mail.php
*    description:    网薪换实物Api
*
*    @author Nili
*    @license Apache v2 License
*    
**/

/**
*  网薪换实物
*/
class Action_Api_Mall extends Action_Base
{

    function __construct() {}

    public function run()
    {
        if (!isset($_GET['m'])) {
            $ret = array('errno' => 1, 'errmsg' => 'm缺失');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        switch ($_GET['m']) {
            case 'list':
                return $this->_getList();
            case 'setState':
                return $this->_setState();
            case 'add':
                return $this->_addItem();
            case 'delete':
                return $this->_deleteItem();
            case 'modify':
                return $this->_modify();
            case 'modify2':
                return $this->_modify2();
            case 'purchaseLog':
                return $this->_purchaseLog();
            case 'setCache':
                return $this->_setCache();
            case 'getCache':
                return $this->_getCache();
        }
        $ret = array('errno'=>1,'errmsg'=>'m参数错误');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
    }

    private function _purchaseLog()
    {
        $ret = array('errno' => 0, 'errmsg' => 'ok', 'data' => array());
        if (!isset($_GET['id']) || !is_numeric($_GET['id']) )
        {
            $ret = array('errno' => 1, 'errmsg' => '商品id异常');
            echo  json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $purchaseLog = Service_Mall_PurchaseLog::getPurchaseLog($_GET['id']);
        $ret['data'] = $purchaseLog;
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _modify2()
    {
        if (!isset($_GET['id']) || 
            !isset($_GET['price']) || 
            !isset($_GET['amount']) || 
            !isset($_GET['type']) || 
            !isset($_GET['limitConds']) || 
            !isset($_GET['endTime']) || 
            !isset($_GET['description']) || 
            !is_numeric($_GET['id']) || 
            !is_numeric($_GET['price']) || 
            !is_numeric($_GET['amount']) || 
            !is_numeric($_GET['type']))
        {
            $ret = array('errno' => 1, 'errmsg' => '参数不全或为空');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if (!$_GET['type'])//竞价商品必须要有竞价结束时间,竞价商品type=0
        {
            if (!isset($_GET['endTime']) || empty($_GET['endTime']))
            {
                $ret = array('errno' => 1, 'errmsg' => '竞价商品必须要有竞价结束时间');
                echo json_encode($ret, JSON_UNESCAPED_UNICODE);
                return false;
            }
        }
        $limitConds = json_encode($_GET['limitConds'], JSON_UNESCAPED_UNICODE);
        Data_Mall::updateItem($_GET['id'], $_GET['price'], $_GET['amount'], $_GET['type'], $limitConds, $_GET['description'], $_GET['endTime']?$_GET['endTime']:null,  isset($_GET['startTime']) ? $_GET['startTime'] : null);
        $cache = Vera_Cache::getInstance();
        $cache->delete('mall_' . $_GET['id'] . '_info');
        $ret = array('errno' => 0, 'errmsg' => 'ok');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _modify()
    {
        if (!isset($_GET['m']) || !isset($_GET['id']) || !is_numeric($_GET['id']))
        {
            $ret = array('errno' => 1, 'errmsg' => '参数不全或为空');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $detail = Data_Mall::getItemByID($_GET['id']);
        $detail = $detail[0];
        if (empty($detail) || $detail['state'] != 0)
        {
            $ret = array('errno' => 1, 'errmsg' => '不存在该商品');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $detail['limitConds'] = json_decode($detail['limitConds'], true);
        $ret = array('errno' => 0, 'errmsg' => 'ok', 'data' => $detail);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getList()
    {
        if (!isset($_GET['state']) || !is_numeric($_GET['state']))
        {
            $ret = array('errno' => 1, 'errmsg' => '参数不全或为空');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $state = $_GET['state'];
        $temp = array();
        $data = new Data_Mall();
        $temp = $data->getList($state);
        if ($temp)
        {
            Vera_Autoload::changeApp('mall');
            foreach ($temp as $key => $each) {
                $each = Service_Helper::getDetail($each['id']);
                $each['usedAmount'] = Data_Db::getUsedAmount($each['id']);
                if ( !isset($each['startTime']) || empty($each['startTime']) || $each['startTime'] == '0000-00-00 00:00:00')
                {
                    $each['startTime'] = $each['onShelfTime'];
                }
                $temp[$key] = $each;
            }
            Vera_Autoload::reverseApp();
        }
        $ret = array('errno' => 0, 'errmsg' => 'ok', 'data' => $temp);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

//上架或下架
    private function _setState()
    {
        if (!isset($_GET['id']) || !isset($_GET['state']) || empty($_GET['id']) || !is_numeric($_GET['state']))
        {
            $ret = array('errno' => 1, 'errmsg' => '参数不全或为空');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $id = $_GET['id'];
        $state = $_GET['state'];
        $data = new Data_Mall();
        $data->setState($id,$state);
        $ret = array('errno' => 0, 'errmsg' => 'ok');
        
        Vera_Autoload::changeApp('mall');
        $detail = Service_Helper::getDetail($id);//确使有该商品的cache
        $key = "mall_" . $id . "_info" ;
        $detail['state'] = $state;
        $cache = Vera_Cache::getInstance();
        $cache->set($key,$detail,time()+3600*24*30);//缓存30天
        Vera_Autoload::reverseApp();

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

//添加商品
    private function _addItem()
    {
        if (!isset($_GET['name']) || !isset($_GET['price']) || !isset($_GET['amount']) || !isset($_GET['type']) || !isset($_GET['limitConds']) || !isset($_GET['description']) || !isset($_GET['pic']) 
            || empty($_GET['name']) || empty($_GET['price']) || !is_numeric($_GET['amount']) || !is_numeric($_GET['type']) || empty($_GET['pic']))
        {
            $ret = array('errno' => 1, 'errmsg' => '参数不全或为空');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        //$pic = "http://wechatyiban.xmu.edu.cn/static/" . $_GET['pic'];
        $pic = json_decode($_GET['pic'], true);
        if ($pic['errno'])
        {
            echo $_GET['pic'];
            return false;
        }
        $pic = "http://wechatyiban.xmu.edu.cn/static/" . $pic['data'];
        $limitConds = json_encode($_GET['limitConds'], JSON_UNESCAPED_UNICODE);
        $data = new Data_Mall();
        $startTime = isset($_GET['startTime']) ? $_GET['startTime'] : null;
        $startTime = empty($_GET['startTime']) ? NULL : $startTime;
        if (!$_GET['type'])//竞价商品必须要有竞价结束时间,竞价商品type=0
        {
            if (!isset($_GET['endTime']) || empty($_GET['endTime']))
            {
                $ret = array('errno' => 1, 'errmsg' => '竞价商品必须要有竞价结束时间');
                echo json_encode($ret, JSON_UNESCAPED_UNICODE);
                return false;
            }
            $endTime = $_GET['endTime'];
            $data->addItem($_GET['name'], $_GET['price'], $_GET['amount'], $_GET['type'], $limitConds, $pic, $_GET['description'], $_GET['endTime'], $startTime);
        }
        else
        {
            $data->addItem($_GET['name'], $_GET['price'], $_GET['amount'], $_GET['type'], $limitConds, $pic, $_GET['description'], null, $startTime);
        }
        $ret = array('errno' => 0, 'errmsg' => 'ok');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _deleteItem()
    {
         $ret = array('errno' => 0, 'errmsg' => 'ok');
        if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
             $ret = array('errno' => 1, 'errmsg' => '无商品id或id非法');
        }
        $tem = Data_Mall::deleteItem($_GET['id']);
        if (!$tem)
        {
             $ret = array('errno' => 1, 'errmsg' => '只能删除新商品');
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _setCache()
    {
        $ret = array('errno' => 1, 'errmsg' => '参数错误');
        if (is_array($_GET))
        {
            $arrKey = array('bobingDailyTimes', 'itemEffectiveDay', 'availableKinds', 'awardTimes');
            foreach ($_GET as $key => $value)
            {
                if (in_array($key, $arrKey) && is_numeric($value))
                {
                    $ret = array('errno' => 0, 'errmsg' => 'ok');
                    Data_Mall::setCache($key, $value);
                }
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getCache(){
        $arrKey = array('bobingDailyTimes' => 5, 'itemEffectiveDay' => 7, 'availableKinds' => 3, 'awardTimes' => 1);
        foreach ($arrKey as $key => $value)
        {
            if ($_GET['key'] == $key)
            {
                $tem = Data_Mall::getCache($key);
                $value = empty($tem) ? $value : $tem;
                echo $value;
                return 0;
            }
        }
    }

}
?>
