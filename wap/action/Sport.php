<?php
/**
*
*    @copyright  Copyright (c) 2015 echo Lin 
*    All rights reserved
*
*    file:            Auth.php
*    description:    权威性验证action
*
*    @author Linjun
*    @license Apache v2 License
*
**/

class Action_Sport extends Action_Base{
    function __construct(){}

    public function run()
    {   
        $m = isset($_GET['m']) ? $_GET['m'] : 'index';
        switch($m){
            case 'index'://展示总成绩页面 
                return $this->_index();
                break;
            case 'detail'://展示详情页
                return $this->_detail();
                break;
            default:
                return $this->_index();
                break;

        }
        return true;
    }

    private function _index(){
//        session_start();
        // $_SESSION['openid'] = 'oqRAFj9zLSEMxJ4zTyfs-KpsixJU';
        // $_SESSION['userInfo'] = array(
        //     'nickname' => 'Echo',
        //     'headimgurl' => 'http://wx.qlogo.cn/mmopen/DMibA1s6klBhRGRIDznpuoyn7PAHqbr7dZibRChVArGmc5sjruMOsYlxj7q8y12Hj729Ac5jpkUM4n7yffChj2Va8HpPViaoJb0/0'
        //     );
        //记录访问日志，包括openID和访问时间戳

        $ret = Service_Sport::rank();

        $today = date('Y');
        $yearList = array();
        while($today > 2013){
            $yearList[] = $today--;
        }

        $orderList = array(
            '1' => '总成绩',
            '2' => '本科生成绩',
            '3' => '研究生成绩',
            '4' => '加油数'
            );

        //var_dump($ret['list']);
        
        // session_start();
        // var_dump($_SESSION['openid']);
        // echo '<br/>';
        // var_dump($_SESSION['userInfo']);
        // echo '<br/>';

        $db = new Data_Sport();
        $message = $db->getMessage();
        if(is_bool($message)){
            $message = array();
        }


        $view = new Vera_View('debug');
        $view->assign('yearList', $yearList);
        $view->assign('orderList', $orderList);
        $view->assign('order', $ret['order']);
        $view->assign('year', $ret['year']);
        $view->assign('row', date('Y-m-d H:i:s'));
        $view->assign('list', $ret['list']);
        $view->assign('message', $message);
        $view->assign('userInfo', isset($_SESSION['userInfo']) ? $_SESSION['userInfo'] : null);
        $view->display('wap/sport/index.tpl');
        return true;

    }


    private function _detail(){
        //记录访问日志，包括openID和访问时间戳

        $list = Service_Sport::getGrade();
        if(is_int($list)){
            $list = array('errno' => -1, 'errmsg' => '操作有误，暂无数据');
        }


        $view = new Vera_View('debug');
        $view->assign('list', $list);
        $view->display('wap/sport/detail.tpl');
        return true;
    }


}
?>