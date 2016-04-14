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
class Service_Sport{
    function __construct(){}

    public static function addItem(){
        $name = isset($_POST['name']) ? $_POST['name'] : null;
        if(empty($name)){
            return 1;//缺少参数
        }

        $sport = new Data_Sport();
        $ret = $sport->addItem($name);
        if(!$ret){
            return 2;//添加项目失败
        }
        return $ret;//修改此处，需要insert id返回， by nili
    }

    public static function addScore(){

        $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : null;
        $type = isset($_POST['type']) ? $_POST['type'] : null;
        $score_arr = isset($_POST['score_arr']) ? $_POST['score_arr'] : null;
        if(empty($item_id) || empty($type) || empty($score_arr) || ($type != 1 && $type != 2)){
            return 1;//参数有误
        }

        $sport = new Data_Sport();
        $ret = $sport->isAddedScore($item_id, $type, date('Y'));

        $score_arr = json_decode($score_arr, true);

        if($ret){//修改积分
            $ret = $sport->updateScore($item_id, $type, $score_arr);
        }else{//添加积分
            $ret = $sport->addScore($item_id, $type, $score_arr); 
        }
        if(!$ret){
            return 3;//添加/修改积分记录失败
        }

        return true;
    }

    public static function getScore(){
        $item_id = isset($_GET['item_id']) ? $_GET['item_id'] : null;
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        if(empty($item_id) || empty($type)){
            return 1;
        }

        $sport = new Data_Sport();

        $ret = $sport->getScore($item_id, $type, date('Y'));
        if(is_bool($ret)){
            return 2;
        }
        return $ret;
    }
}
?>