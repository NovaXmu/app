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

    public static function rank(){
        $sport = new Data_Sport();
        //获取积分排名情况
        $order = isset($_GET['order']) ? $_GET['order'] : null;
        $year = isset($_GET['year']) ? $_GET['year'] : null;
        if(empty($order) || $order > 4 || $order < 1){
            $order = 1;
        }
        if(empty($year) || $year < 2014){
            $year = date('Y');
        }

        $ret = array(
            'year' => $year,
            'order' => $order,
            'list' => array()
            );

        $ret['list'] = $sport->getRank($year, $order);
        if(is_bool($ret['list'])){
            return $ret;
        }

        //处理积分数字 转换成数组
        foreach($ret['list'] as $key => $value){
            $ret['list'][$key]['jifen_bk_result'] = self::explodeJifen($value['jifen_bk']);
            $ret['list'][$key]['jifen_yjs_result'] = self::explodeJifen($value['jifen_yjs']);
            $ret['list'][$key]['sum_result'] = self::explodeJifen($value['sum']);
        }

        //获取用户点赞情况
        $xueyuanArr = array();
        foreach($ret['list'] as $key => $value){
            $ret['list'][$key]['isPraised'] = false;//初始化为false
            $xueyuanArr[] = $value['id'];
        }

        $openid = isset($_SESSION['openid']) ? $_SESSION['openid'] : null;
        if(empty($openid)){//没有在微信端打开
            return $ret;
        }

        $xueyuanArr = $sport->isCheered($openid, $xueyuanArr, $year, true);
        if(!is_bool($xueyuanArr)){
            foreach($ret['list'] as $key => $value)
                if(in_array($value['id'], $xueyuanArr))
                    $ret['list'][$key]['isPraised'] = true;
        }

        return $ret;
    }

    public static function getGrade(){
        $xueyuan_id = isset($_GET['xueyuan_id']) ? $_GET['xueyuan_id'] : null;
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        if(empty($xueyuan_id) || empty($year)){
            return 1;//参数错误
        }
        $sport = new Data_Sport();
        $xueyuan = array();
        $xueyuan['info'] = $sport->getXueyuanInfo($xueyuan_id);
        if(!$xueyuan['info']){
            return 2;//学院不存在
        }

        $grade = $sport->getGrade($xueyuan_id, $year);
        if(is_bool($grade)){
            return 3;//获取成绩失败
        }

        $lastYearGrade = $sport->getGrade($xueyuan_id, $year - 1);

        $item = $sport->getItems();
        if(is_bool($item)){
            return 4;//获取项目失败
        }

        //关联成绩与项目
        $xueyuan['info']['jifen_bk'] = 0;
        $xueyuan['info']['jifen_yjs'] = 0;
        foreach($item as $key => $value){
            $xueyuan['item'][$value['id']] = array(
                'name' => $value['name'],
                'grade' => array(
                    'bk' => isset($grade[$value['id']][1]) ? $grade[$value['id']][1] : '暂无成绩',
                    'yjs' => isset($grade[$value['id']][2]) ? $grade[$value['id']][2] : '暂无成绩'
                    )
                );
            $xueyuan['info']['jifen_bk'] += isset($grade[$value['id']][1]) ? $grade[$value['id']][1] : 0;
            $xueyuan['info']['jifen_yjs'] += isset($grade[$value['id']][2]) ? $grade[$value['id']][2] : 0;
        }

        foreach ($xueyuan['item'] as $itemId => $row) {
            foreach ($row['grade'] as $key => $value) {
                $tmp = ($key == 'bk') ? 1 : 2;
                if ($value == '暂无成绩'){
                    $xueyuan['item'][$itemId]['grade'][$key . '_change'] = '';
                } else if (isset($lastYearGrade[$itemId][$tmp])){
                    $xueyuan['item'][$itemId]['grade'][$key . '_change'] = $value - $lastYearGrade[$itemId][$tmp];
                } else {
                    $xueyuan['item'][$itemId]['grade'][$key . '_change'] = $value;
                }
            }
        }
        return $xueyuan;
    }

    public static function cheer(){
        $xueyuan_id = isset($_GET['xueyuan_id']) ? $_GET['xueyuan_id'] : null;
        $year = isset($_GET['year']) ? $_GET['year'] : null;
        if(empty($xueyuan_id) || empty($year)){
            return 2;// error 1, 参数错误
        }
        if($year < date('Y')){
            return 1;//error 1, 成绩为以前的
        }

        if(!isset($_SESSION['openid']) || empty($_SESSION['openid'])){
            return 3;
        }

        $sport = new Data_Sport();

        //$openid = $_SESSION['openid'];
        /*暂时不需要验证是否绑定厦大账号“*/
        // if(empty($openid) || !$db->isLinkedXmu($openid)){
        //     return 2;//error 2, 没有在微信端打开或没有绑定厦大账号
        // }

        $isCheered= $sport->isCheered($_SESSION['openid'], $xueyuan_id, date('Y'));

        $ret = $sport->addOrDeleteCheer($_SESSION['openid'], $xueyuan_id, $isCheered);
        if(!$ret){
            if($isCheered)
                return 4;//error, 取消加油失败
            else
                return 5;//error,加油失败
        }

        return true;
    }

    public static function addMessage(){
        if(!isset($_SESSION['openid']) || empty($_SESSION['openid'])){
            return 1;
        }
        if(!isset($_SESSION['userInfo']) || empty($_SESSION['userInfo'])){
            return 2;
        }
        $content = isset($_POST['content']) ? $_POST['content'] : null;
        //$content = isset($_GET['content']) ? $_GET['content'] : null;
        if(empty($content)){
            return 3;
        }
        $db = new Data_Sport();
        $ret = $db->addMessage($_SESSION['openid'], $content);
        if(!$ret){
            return 4;
        }
        return true;
    }

    public static function getMoreMessage(){
        $index = isset($_POST['index']) ? $_POST['index'] : null;
        if(empty($index) || !is_int(intval($index))){
            return 1;
        }

        $db = new Data_Sport();
        $list = $db->getMessage($index);
        if(is_bool($list)){
            return 2;
        }

        return $list;
    }

    private static function explodeJifen($num){
        $result = array(0,0,0,0);
        for($i = 3; $num > 0; $i--){
            $result[$i] = $num % 10;
            $num = intval($num/10);
        }
        return $result;
    }
}
?>