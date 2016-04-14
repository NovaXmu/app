<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Topic.php
*   description:      Data for Topic.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Data_Topic{
    function __construct(){}

    public function getHotTopic(){
        $cache = Vera_Cache::getInstance();
        $hotList = $cache->get('meet_hotTopicID');
        if(!$hotList){
            return false;
        }
        foreach($hotList as $key => $value){
            if($value != 0){
                $hotList[$key] = $this->getTopicInfo($value);
            }else{
                unset($hotList[$key]);
            }
        }

        return $hotList;
    }

    public function setHotTopic($topic_id){

        $cache = Vera_Cache::getInstance();
        $key_hot = 'meet_hotTopicID';
        $arr_hot = $cache->get($key_hot);
        if(!$arr_hot){
            $arr_hot = array(0,0,0);
        }

        $topic = $cache->get('meet_topic_' . $topic_id . '_info');
        if(!$topic){
            return false;
        }

        //如果热门话题id中有当前话题的id
        if(in_array($topic_id, $arr_hot)){
            foreach($arr_hot as $key => $value){
                if($value == $topic_id){//找到当前话题id在热门话题id的位置
                    //比较当前话题的viewed和排在当前话题之前的热门话题的viewed
                    for($i = $key; $i > 0; $i--){
                        $hot = $cache->get('meet_topic_' . $arr_hot[$i-1] . '_info');
                        if($hot['viewed'] <= $topic['viewed']){
                            $arr_hot[$i] = $arr_hot[$i-1];
                            $arr_hot[$i-1] = $topic_id;
                        }
                    }
                }
            }
        }else{//如果热门话题id中没有当前话题的id
            for($i = 0; $i < 3; $i++){
                if($i == 0 && $arr_hot[$i] == 0){
                    $arr_hot[$i] = $topic_id;
                    break;
                }else{
                    $hot = $cache->get('meet_topic_' . $arr_hot[$i] . '_info');
                    if($hot['viewed'] <= $topic['viewed']){
                        for($j = 2; $j > $i; $j--){
                            $arr_hot[$j] = $arr_hot[$j-1];
                        }
                        $arr_hot[$i] = $topic_id;
                        break;
                    }
                }
            }
        }

        $cache->set($key_hot, $arr_hot, time() + 3600 * 24 *30);

        return true;
    }

    public function getMoreTopic($index, $num){

        $cache = Vera_Cache::getInstance();
        $hot_id = $cache->get('meet_hotTopicID');
        if(!$hot_id){
            $conds = NULL;
        }else{
            $conds = 'id NOT IN (' . implode(',', $hot_id) . ')';
        }

        if($index == 0){
            $appends = 'order by time desc limit '. $index .',' . $num;
        }else{
            $appends = 'order by time desc limit '. ($index * $num - 3) .',' . $num;
        }

        $db = Vera_Database::getInstance();
        $list = $db->select('meet_Topic', 'id', $conds, NULL, $appends);
        if(is_bool($list)){
            return false;
        }

        foreach($list as $key => $value){
            $list[$key] = $this->getTopicInfo($value['id']);
        }

        return $list;
    }

    public function getTopicInfo($topic_id, $addViewed = false){

        $key = 'meet_topic_' . $topic_id . '_info';
        $cache = Vera_Cache::getInstance();
        $topic = $cache->get($key);

        if(!$topic){
            $db = Vera_Database::getInstance();
            $conds = array('id' => $topic_id);
            $ret = $db->select('meet_Topic', '*', $conds);
            if(is_bool($ret) || !isset($ret[0]['id'])){
                return false;
            }
            $topic = $ret[0];
            $topic['viewed'] = 0;
            $topic['commentCount'] = $db->selectCount('meet_Comment', array('topic_id' => $topic_id));
            $topic['praiseCount'] = $db->selectCount('meet_Praise', array('obj_id' => $topic_id, 'obj_type' => 1));
            $ret = $cache->set($key, $topic, time() + 3600 * 24 * 30);
        }

        if($addViewed){
            $topic['viewed'] += 1;
            $cache->set($key, $topic, time() + 3600 * 24 * 30);
        }

        $topic['isPraised'] = $this->isPraised($_SESSION['yb_user_info']['yb_userid'], $topic['id'], 1);

        return $topic;
    }

    public function getCommentDetail($comment_id){
        $db = Vera_Database::getInstance();
        $conds = array('comment_id' => $comment_id);
        $ret = $db->select('meet_Comment', '*', $conds);
        if(is_bool($ret)){
            return false;
        }

        return $ret;
    }

    public function getCommentInfo($comment_id){
        $conds = array('id' => $comment_id);
        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_Comment', '*', $conds);
        if(is_bool($ret)){
            return false;
        }

        return $ret[0];
    }

    public function getCommentList($topic_id, $index){
        $db = Vera_Database::getInstance();
        $conds = array(
            'topic_id' => $topic_id,
            'comment_id' => 0
            );
        $appends = 'order by time desc limit ' . ($index*5) . ',5';
        $ret = $db->select('meet_Comment', '*', $conds, NULL, $appends);

        if(is_bool($ret)){
            return false;
        }

        foreach($ret as $key => $value){
            $ret[$key]['praiseCount'] = $db->selectCount('meet_Praise', array('obj_id' => $value['id'], 'obj_type' => 2));
            $ret[$key]['commentCount'] = $db->selectCount('meet_Comment', array('comment_id' => $value['id']));
            $ret[$key]['isPraised'] = $this->isPraised($_SESSION['yb_user_info']['yb_userid'], $value['id'], 2);
        }

        return $ret;
    }

    public function addTopic($user_ybid, $title, $content){
        $data = array(
            'user_ybid' => $user_ybid,
            'title' => $title,
            'content' => $content,
            'time' => date('Y-m-d H:i:s')
            );

        $db = Vera_Database::getInstance();
        $ret = $db->insert('meet_Topic', $data);
        if(!$ret){
            return false;
        }

        return true;
    }

    public function isPraised($user_ybid, $obj_id, $obj_type){
         $conds = array(
            'user_ybid' => $user_ybid,
            'obj_id' => $obj_id,
            'obj_type' => $obj_type
            );

         $db = Vera_Database::getInstance();
         $ret = $db->select('meet_Praise', '*', $conds);
         if(!$ret){
            return false;
         }

         return true;
    }

    public function addPraise($user_ybid, $obj_id, $obj_type){
        $data = array(
            'user_ybid' => $user_ybid,
            'obj_id' => $obj_id,
            'obj_type' => $obj_type,
            'time' => date('Y-m-d H:i:s')
            );

        $db = Vera_Database::getInstance();
        $ret = $db->insert('meet_Praise', $data);
        if(!$ret){
            return false;
        }

        if($obj_type == 1){
            $cache = Vera_Cache::getInstance();
            $key = 'meet_topic_'.$obj_id.'_info';
            $topic = $cache->get($key);
            $topic['praiseCount']++;
            $cache->set($key, $topic, time() + 3600 * 24 * 30);
        }

        return true;
    }

    public function deletePraise($user_ybid, $obj_id, $obj_type){
        $conds = array(
            'user_ybid' => $user_ybid,
            'obj_id' => $obj_id,
            'obj_type' => $obj_type
            );

        $db = Vera_Database::getInstance();
        $ret = $db->delete('meet_Praise', $conds);
        if(!$ret){
            return false;
        }

        if($obj_type == 1){
            $cache = Vera_Cache::getInstance();
            $key = 'meet_topic_'.$obj_id.'_info';
            $topic = $cache->get($key);
            $topic['praiseCount']--;
            $cache->set($key, $topic, time() + 3600 * 24 * 30);
        }

        return true;
    }

    public function addComment($topic_id, $from_user_ybid, $content, $to_user_ybid, $comment_id = 0){

        $data = array(
            'topic_id' => $topic_id,
            'from_user_ybid' => $from_user_ybid,
            'content' => $content,
            'to_user_ybid' => $to_user_ybid,
            'comment_id' => $comment_id,
            'time' => date('Y-m-d H:i:s'),
            'isRead' => -1
            );

        $db = Vera_Database::getInstance();
        $ret = $db->insert('meet_Comment', $data);
        if(!$ret){
            return false;
        }

        if($comment_id == 0){
            $cache = Vera_Cache::getInstance();
            $key = 'meet_topic_'.$topic_id.'_info';
            $topic = $cache->get($key);
            $topic['commentCount']++;
            $cache->set($key, $topic, time() + 3600 * 24 * 30);
        }

        return true;
    }

    public function deleteTopic($topic_id){
        $conds = array('id' => $topic_id);
        $db = Vera_Database::getInstance();
        $ret = $db->delete('meet_Topic', $conds);
        if(!$ret){
            return false;
        }

        return true;
    }

    public function getMyUnreadCommentCount($user_ybid){
        $db = Vera_Database::getInstance();
        $conds = array('to_user_ybid' => $user_ybid, 'isRead' => -1);
        $count = $db->selectCount('meet_Comment', $conds);
        if(is_bool($count)){
            return false;
        }

        return $count;
    }

    public function getMyComment($user_ybid, $index = 0){
        $db = Vera_Database::getInstance();
        $conds = array('to_user_ybid' => $user_ybid);
        $appends = 'order by time desc';
        $appends .= ' limit '. ($index * 10) .',10';
        $ret = $db->select('meet_Comment', '*', $conds, NULL, $appends);
        if(is_bool($ret)){
            //var_dump($db->getLastSql());
            return array();
        }
        if(count($ret) != 0){
            $topic_idList = array();
            foreach($ret as $key => $value){
                $topic_idList[] = $value['topic_id'];
            }
            $topic_idList = array_unique($topic_idList);
            $conds = 'id IN (' . implode(',', $topic_idList) . ')';
            $topic_idList = $db->select('meet_Topic', 'id, title', $conds);
            if(is_bool($topic_idList)){
                //记Log
            }
            $topic_titleList = array();
            foreach($topic_idList as $key => $value){
                $topic_titleList[$value['id']] = $value['title'];
            }
            foreach($ret as $key => $value){
                $ret[$key]['title'] = $topic_titleList[$value['topic_id']];
            }
        }
        return $ret;
    }

    public function setCommentReaded($user_ybid){
        $db = Vera_DataBase::getInstance();
        $conds = array('to_user_ybid' => $user_ybid);
        $row = array('isRead' => 1);
        $ret = $db->update('meet_Comment', $row, $conds);
        if(!$ret){
            //记Log
            return false;
        }
        return true;
    }

}
?>