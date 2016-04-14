<?php
/**
*
*    @copyright  Copyright (c) 2015 echo Lin 
*    All rights reserved
*
*    file:            Sport.php
*    description:    权威性验证action
*
*    @author Linjun
*    @license Apache v2 License
*
**/

class Action_Api_Sport extends Action_Base{
    function __construct(){}

    public function run(){
        $m = $_GET['m'];
        switch($m){
            case 'index':
                return $this->_index();
                break;
            case 'addItem'://cms 添加项目 test pass
                return $this->_addItem();
                break;
            case 'addScore'://cms 加分 test pass
                return $this->_addScore();
                break;
            case 'history':
                return $this->_history();
            case 'getScore':
                return $this->_getScore();
                break;
            default:
                break;
        }
        $ret = array('errno' => '1', 'errmsg' => '参数不对');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _index(){
        $db = new Data_Sport();
        $list = array(
            'itemList' => $db->getItemList(),
            'xueyuanList' => $db->getXueyuanList(),
            'scoreList' => $db->getScore(1, 1, date('Y'))
            );
        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _addItem(){
        $addItem = Service_Sport::addItem();
        $ret = array('errno' => 0, 'errmsg' => 'OK', 'data' => $addItem);
        if(is_int($addItem)){
            switch($addItem){
                case 1:
                    $ret['errno'] = -1;
                    $ret['errmsg'] = '参数错误，快去找前端大大吧~';
                    break;
                case 2:
                    $ret['errno'] = -1;
                    $ret['errmsg'] = '啊哈！ 添加比赛项目失败了？！ 快去找后端大大吧~';
                    break;
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _addScore(){
        $addScore = Service_Sport::addScore();
        $ret = array('errno' => 0, 'errmsg' => 'OK');
        if(is_int($addScore)){
            $ret['errno'] = -1;
            switch($addScore){
                case 1:
                    $ret['errmsg'] = '参数错误，快去找前端大大吧~';
                    break;
                case 2:
                    $ret['errmsg'] = '啊哈！ 添加或修改积分失败了？！ 快去找后端大大吧~';
                    break;
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    /**
     * 历史成绩接口
     * @return bool 
     * @author nili 
     */
    private function _history(){
        $db = new Data_Sport();
        $allXueYuan = $db->getXueyuanList();
        $xueyuanIds = isset($_GET['xueyuanIds']) ? json_decode($_GET['xueyuanIds'], true) : array_column($allXueYuan,'id');
        $xueyuanIds = $xueyuanIds ? $xueyuanIds : array_column($allXueYuan,'id');
        $years = isset($_GET['years']) ? json_decode($_GET['years']) : array(date('Y'));
        $years = $years ? $years : array(date('Y'));
        if (!isset($_GET['item']) || !is_numeric($_GET['item'])){
            echo json_encode(array('errno' => '1', 'errmsg' => '参数不对'), JSON_UNESCAPED_UNICODE);
            return true;
        }
        if (!isset($_GET['type']) || !is_numeric($_GET['type'])){
            $historyData = $db->getHistoryData($xueyuanIds, $_GET['item'], $years) ;
            foreach ($historyData as $year => $oneYearData) {
                $tmp = array();
                foreach ($oneYearData as $key => &$row) {
                    if (isset($tmp[$row['xueyuan_id']])){
                        $tmp[$row['xueyuan_id']]['score'] += $row['score'];
                    } else {
                        $tmp[$row['xueyuan_id']] = $row;
                    }
                }
                $historyData[$year] = $tmp;
            }
        } else {
            $historyData = $db->getHistoryData($xueyuanIds, $_GET['item'], $years, $_GET['type']) ;
        }
        $xueyuanList = array_column($allXueYuan, 'xueyuan');
        echo json_encode(array('errno' => '0', 'errmsg' => 'ok', 'data' => array('historyData' => $historyData, 'xueyuan' => $xueyuanList)), JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getScore(){
        $getScore = Service_Sport::getScore();
        $ret = array('errno' => 0, 'errmsg' => 'OK');
        if(is_int($getScore)){
            $ret['errno'] = -1;
            switch($getScore){
                case 1:
                    $ret['errmsg'] = '参数错误，快去找前端大大吧~';
                    break;
                case 2:
                    $ret['errmsg'] = '获取失败成绩失败了';
                    break;
            }
        }else{
            $ret = $getScore;
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

}
?>