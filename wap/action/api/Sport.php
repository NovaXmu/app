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

class Action_Api_Sport extends Action_Base{
    function __construct(){}

    public function run(){
        $m = $_GET['m'];
        switch($m){
            case 'cheer'://加油 test pass
                return $this->_cheer();
                break;
            case 'update'://实时
                return $this->_update();
                break;
            case 'addMessage'://
                return $this->_addMessage();
                break;
            case 'getMoreMessage':
                return $this->_getMoreMessage();
                break;
            default:
                break;
        }
        $ret = array('errno' => '1', 'errmsg' => '参数不对');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _cheer(){
        $cheer = Service_Sport::cheer();
        $ret = array('errno' => 0, 'errmsg' => 'OK');
        if(is_int($cheer)){
            $ret['errno'] = -1;
            switch($cheer){
                case 1:
                    $ret['errmsg'] = '不要为以前的成绩点赞！';
                    break;
                case 2:
                    $ret['errmsg'] = '参数错误，快去找前端大大拜年吧~';
                    break;
                case 3:
                    $ret['errmsg'] = '请从微信进入，关注厦大易班，再在其中点击菜单按钮进入积分榜，才可以加油哦~';
                    break;
                case 4:
                    $ret['errmsg'] = '啊哈！ 取消加油失败了？！ 快去找后端大大吧~';
                    break;
                case 5:
                    $ret['errmsg'] = '啊哈！ 添加加油失败了？！ 快去找后端大大吧~';
                    break;
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _update(){
        $ret = Service_Sport::rank();
        echo json_encode($ret['list'], JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _addMessage(){
        $add = Service_Sport::addMessage();
        $ret = array('errno' => 0, 'errmsg' => 'OK');
        if(is_int($add)){
            $ret['errno'] = -1;
            switch($add){
                case 1:
                    $ret['errmsg'] = '请从微信进入，关注厦大易班，再在其中点击菜单按钮进入积分榜，才可以留言哦~';
                    break;
                case 2:
                    $ret['errmsg'] = '请从微信进入，关注厦大易班，再在其中点击菜单按钮进入积分榜，才可以留言哦~';
                    break;
                case 3:
                    $ret['errmsg'] = '留言内容不能为空，能不能多写点儿';
                    break;
                case 4:
                    $ret['errmsg'] = '不支持IOS的emoji表情，换颜表情吧╭(╯^╰)╮';
                    break;
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getMoreMessage(){
        $list = Service_Sport::getMoreMessage();
        $ret = array('errno' => 0, 'errmsg' => 'OK');
        if(is_int($list)){
            $ret['errno'] = -1;
            switch($list){
                case 1:
                    $ret['errmsg'] = '参数错误，快去找前端大大吧';
                    break;
                case 2:
                    $ret['errmsg'] = '获取留言失败,快去找后端大大吧';
                    break;
            }
        }else{
            $ret = $list;
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    
}
?>