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
class Action_Api_Public_Mall extends Action_Base
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
            case 'exchange1':
                return $this->_exchange1();
            case 'exchange2':
                return $this->_exchange2();
        }
        $ret = array('errno'=>1,'errmsg'=>'m参数错误');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
    }

    private function _exchange1()
    {
        $ret = array('errno' => 1, 'errmsg' => '', 'data' => array());
        if (!isset($_GET['token']))
        {
            $ret = array('errno' => 1, 'errmsg' => '无token');
        }
        else { 
            $tem = Data_Mall::checkTokenIsused($_GET['token'], $ret['data']);
            if (!$tem)
            {
                $ret['errno'] = 0;
                $ret['errmsg'] = 'ok';
            }
            else
            {
                $ret['errmsg'] = $tem;
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _exchange2()
    {
        $ret = array('errno' => 0, 'errmsg' => 'ok');
        if (!isset($_GET['logID']))
        {
            $ret = array('errno' => 1, 'errmsg' => '无logID');
        }
        else
        {
            $tem = Data_Mall::setTokenUsed($_GET['logID']);
            if (!$tem)
            {
                $ret = array('errno' => 1, 'errmsg' => '设置失败');
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

}
?>
