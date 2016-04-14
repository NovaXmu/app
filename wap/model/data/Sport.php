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

    public function getRank($year, $order){
        $db = Vera_Database::getInstance();
        //获取所有学院
        $xueyuan = $db->select('sport_Xueyuan', '*');
        if(is_bool($xueyuan)){
            //var_dump($db->getLastSql());
            return false;
        }
        //获取成绩总和
        $conds = 'time like "' . $year . '%"';
        $appends = 'group by xueyuan_id, type';
        $score = $db->select('sport_ScoreLog','xueyuan_id, type, SUM(score) score', $conds, NULL, $appends);
        if(is_bool($score)){
            //var_dump($db->getLastSql());
            return false;
        }
        //获取点赞
        $appends = 'group by xueyuan_id';
        $dianzan = $db->select('sport_CheerLog', 'xueyuan_id, COUNT(openid) dianzan', $conds, NULL, $appends);
        if(is_bool($dianzan)){
            //var_dump($db->getLastSql());
            return false;
        }
        //var_dump($dianzan);
        //合并数据
        $list = array();
        foreach($xueyuan as $key => $value){
            $list[$value['id']] = array(
                'id' => $value['id'],
                'xueyuan' => $value['xueyuan'],
                'log_id' => $value['id'],
                'jifen_bk' => 0,
                'jifen_yjs' => 0,
                'dianzan' => 0,
                'sum' => 0
                );
        }
        foreach($score as $key => $value){
            if($value['type'] == 1)
                $list[$value['xueyuan_id']]['jifen_bk'] = $value['score'];
            else
                $list[$value['xueyuan_id']]['jifen_yjs'] = $value['score'];
        }
        foreach($dianzan as $key => $value){
            $list[$value['xueyuan_id']]['dianzan'] = $value['dianzan'];
        }

        $arr = array();
        foreach($list as $key => $value){
            $list[$key]['sum'] = $value['jifen_bk'] + $value['jifen_yjs'];
            $arr[] = $list[$key];
        }

        $id = array();
        $sort = array();
        switch($order){
            case 1://总成绩排序
                foreach($arr as $key => $value){
                    $id[$key] = $value['id'];
                    $sort[$key] = $value['sum'];
                }
                break;
            case 2://本科生成绩排序
                foreach($arr as $key => $value){
                    $id[$key] = $value['id'];
                    $sort[$key] = $value['jifen_bk'];
                }
                break;
            case 3://研究生成绩排序
                foreach($arr as $key => $value){
                    $id[$key] = $value['id'];
                    $sort[$key] = $value['jifen_yjs'];
                }
                break;
            case 4://加油排序
                foreach($arr as $key => $value){
                    $id[$key] = $value['id'];
                    $sort[$key] = $value['dianzan'];
                }
                break;
        }
        //排序
        array_multisort($sort, SORT_DESC, $id, SORT_DESC, $arr);

        return $arr;
    }

    public function isCheered($openid, $xueyuan_id, $year, $isArray = false){
        $db = Vera_Database::getInstance();
        $conds = "(time like '$year%') AND (openid = '". $openid ."') AND deleted = 0 AND";
        //查询单人对单学院是否点赞过
        if(!$isArray){
            $conds .= '(xueyuan_id = ' . $xueyuan_id . ')';
            $ret = $db->select('sport_CheerLog', '*', $conds);
            if(is_bool($ret) || !isset($ret['0'])){
                return false;
            }
            return true;
        }
        //查询单人对所有学院的点赞情况
        $xueyuan = implode(',', $xueyuan_id);
        $conds .= '(xueyuan_id IN (' . $xueyuan . ')) AND deleted = 0';
        $ret = $db->select('sport_CheerLog', '*', $conds);
        if(is_bool($ret)){
            //var_dump($db->getLastSql());
            return false;
        }

        $list = array();
        foreach($ret as $key => $value){
            $list[] = $value['xueyuan_id'];
        }

        return $list;
    }

    public function isLinkedXmu($openid){
        $db = Vera_Database::getInstance();
        $conds = 'wechatOpenid = ' . $openid;
        $ret = $db->select('User', '*', $conds);
        if(is_bool($ret) || !isset($ret['0'])){
            return false;
        }

        if($ret['0']['isLinkedXmu'] != 1){
            return false;
        }

        return true;
    }

    public function addOrDeleteCheer($openid, $xueyuan_id, $isCheered = false){
        $db = Vera_Database::getInstance();
        $num= 1;
        if(!$isCheered){
            $rows = array(
                'openid' => $openid,
                'xueyuan_id' => $xueyuan_id,
                'time' => date('Y-m-d H:i:s')
                );
            $ret = $db->insert('sport_CheerLog', $rows);
        }else{
            $num = -1;
            $conds = "(time like '" . date('Y') . "%') AND openid = '$openid' AND ".'(xueyuan_id = ' . $xueyuan_id . ')';
            //$ret = $db->delete('sport_CheerLog', $conds);
            $ret = $db->update('sport_CheerLog', 'deleted=1', $conds);
        }
        if(is_bool($ret)){
            //echo $db->getLastSql();
            return false;
        }
        return true;
    } 

    public function getXueyuanInfo($xueyuan_id){
        $db = Vera_Database::getInstance();
        $ret = $db->select('sport_Xueyuan', '*', "id = $xueyuan_id");
        if(is_bool($ret) || !isset($ret[0]['id'])){
            return false;
        }
        $ret[0]['log_id'] = $ret[0]['id'];
        return $ret[0];
    }

    public function getGrade($xueyuan_id, $year){
        $db = Vera_Database::getInstance();
        $conds = 'xueyuan_id = ' . $xueyuan_id . ' AND time like "' . $year . '%"';
        $ret = $db->select('sport_ScoreLog', '*', $conds);
        if(is_bool($ret)){
            return false;
        }
        $grade = array();
        foreach($ret as $key => $value){
            $grade[$value['item_id']][$value['type']] = $value['score'];
        }
        return $grade;
    }

    public function getItems(){
        $db = Vera_Database::getInstance();
        $ret = $db->select('sport_Item', '*');
        if(is_bool($ret)){
            return false;
        }
        return $ret;
    }

    public function getMessage($index = 1){
        $db = Vera_Database::getInstance();
        $appends = 'order by time desc limit '. (($index-1) * 5) .',5';
        $ret = $db->select('sport_Message','*',NULL,NULL,$appends);
        if(is_bool($ret)){
            return false;
        }
        return $ret;
    }

    public function addMessage($openid, $content){
        $db = Vera_Database::getInstance();
        $row = array(
            'openid' => $openid,
            'nickname' => isset($_SESSION['userInfo']['nickname']) ? $_SESSION['userInfo']['nickname'] : 'guest',
            'headimgurl' => isset($_SESSION['userInfo']['headimgurl']) ? $_SESSION['userInfo']['headimgurl'] : 'guest',
            'content' => $content,
            'time' => date('Y-m-d H:i:s')
            );
        $ret = $db->insert('sport_Message', $row);
        if(is_bool($ret)){
            $row['nickname'] = 'emoji';
            $ret = $db->insert('sport_Message', $row);
            if ($ret){
                return true;
            }
            Vera_Log::addLog('tmp', date('Y-m-d H:i:s') . ' ' . Vera_Database::getLastSql());
            return false;
        }
        return true;
    }

}
?>