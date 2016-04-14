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

class Data_Sport{

    function __construct(){}

    public function getXueyuanList(){
        $db = Vera_Database::getInstance();
        $ret = $db->select('sport_Xueyuan', '*');
        if(is_bool($ret)){
            return false;
        }
        return $ret;
    }

    public function getItemList(){
        $db = Vera_Database::getInstance();
        $ret = $db->select('sport_Item', '*');
        if(is_bool($ret)){
            return false;
        }
        return $ret;
    }

    public function addItem($name){
        $db = Vera_Database::getInstance();
        $ret = $db->insert('sport_Item', array('name' => $name, 'time' => date('Y-m-d H:i:s')));
        if(!$ret){
            return false;
        }
        return mysqli_insert_id($db->mysql);
    }

    public function isAddedScore($item_id, $type, $year){
        $db = Vera_Database::getInstance();
        $conds = 'item_id = ' .$item_id . ' AND type=' . $type . ' AND (time like "' . $year . '%")';
        $ret = $db->select('sport_ScoreLog', '*', $conds);
        if(is_bool($ret)){
            //记Log
        }
        if(empty($ret)){
            return false;
        }
        return true;

    }

    public function addScore($item_id, $type, $score_arr){
        $db = Vera_Database::getInstance();
        $row = array(
            'item_id' => $item_id,
            'type' => $type,
            'time' => date('Y-m-d H:i:s'),
            'score' => 0,
            'xueyuan_id' => 0
            );
        $xueyuan = $db->select('sport_Xueyuan', '*');
        foreach($score_arr as $key=>$value){
            $row['xueyuan_id'] = $xueyuan[$key]['id'];
            $row['score'] = $value;
            $ret = $db->insert('sport_ScoreLog', $row);
            if(!$ret){
                //记Log
                echo $db->getLastSql();
                return false;
            }
        }
        return true;
    }

    /**
     * 获取历史成绩
     * @param  array $xueyuanIds 学院id
     * @param  int $item       项目id
     * @param  array $years      年份
     * @return array             成绩
     * @author nili 
     */
    public function getHistoryData($xueyuanIds, $item, $years, $type = null)
    {
        $db = Vera_Database::getInstance();
        $tmp = $type ? "type={$type} AND " : '';
        foreach ($years as $year) {
            $where = $tmp . "xueyuan_id in (" . implode(',', $xueyuanIds) . ") AND item_id = {$item} AND time LIKE '" . $year . "%'";
            $ret[$year] = $db->select('sport_ScoreLog', '*', $where);
        }
        return $ret;
    }


    public function updateScore($item_id, $type, $score_arr){
        $db = Vera_Database::getInstance();
        $row = array(
            'time' => date('Y-m-d H:i:s'),
            'score' => 0,
            );
        $xueyuan = $db->select('sport_Xueyuan', '*');
        foreach($score_arr as $key=>$value){
            $row['score'] = $value;
            $conds = 'item_id = ' . $item_id . ' AND type = ' . $type . ' AND xueyuan_id = ' . $xueyuan[$key]['id'] . ' AND time like"' . date('Y') . '%"';
            $ret = $db->update('sport_ScoreLog', $row, $conds);
            if(!$ret){
                //记Log
                //echo $db->getLastSql();
                return false;
            }
        }
        return true;
    }

    public function getScore($item_id, $type, $year){
        $db = Vera_Database::getInstance();
        $conds = 'item_id = ' .$item_id . ' AND type=' . $type . ' AND (time like "' . $year . '%")';
        $appends = 'order by xueyuan_id';
        $ret = $db->select('sport_ScoreLog', '*', $conds, NULL, $appends);
        //echo $db->getLastSql();
        if(is_bool($ret)){
            //echo $db->getLastSql();
            return false;
        }
        return $ret;
    }
}
?>