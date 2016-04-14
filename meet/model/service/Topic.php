<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Topic.php
*   description:      Service for Topic.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Service_Topic{
    function __construct(){}

/**
 * 获取热门话题（3个）
 *
 * @return array list
 */
    public static function getHotTopic(){
        $db = new Data_Topic();
        $hotList = $db->getHotTopic();
        if(!$hotList){
            return array();
        }
        return $hotList;
    }

/**
 * 获取更多话题 按照发布时间获取前5*index个
 *
 * @param  int $index 
 * @return array        
 */
    public static function getMoreTopic($index, $num = 5){
        if(empty($index) && $index != 0){
            return 1;
        }

        $db = new Data_Topic();
        $list = $db->getMoreTopic($index, $num);

        if(is_bool($list)){
            if($index == 0){
                return array();
            }

            return 2;
        }

        return $list;
    }

    public static function getMoreComment($topic_id, $index){
        if(empty($topic_id) || (empty($index) && $index != 0)){
            return 1;
        }

        $db = new Data_Topic();
        $list = $db->getCommentList($topic_id, $index);
        if(is_bool($list)){
            return 2;
        }

        if(count($list) != 0){
            $db = new Data_User();
            $users = array();
            foreach($list as $key => $value){
                $users[] = $value['from_user_ybid'];
            }
            $users = array_unique($users);
            foreach($users as $key => $value){
                $users[$key] = array('user_ybid' => $value);
            }
            $users = $db->getUserNicknameByArr($users);
            foreach($list as $key => $value){
                $list[$key]['nickname'] = $users[$value['from_user_ybid']];
            }
        }

        return $list;
    }

    public static function getCommentDetail($comment_id){
        if(empty($comment_id)){
            return 1;
        }

        $db = new Data_Topic();
        $list = $db->getCommentDetail($comment_id);
        if(is_bool($list)){
            return 2;
        }

        if(count($list) != 0){
            $db = new Data_User();
            $users = array();
            foreach($list as $key => $value){
                $users[] = $value['from_user_ybid'];
                $users[] = $value['to_user_ybid'];
            }
            $users = array_unique($users);
            foreach($users as $key => $value){
                $users[$key] = array('user_ybid' => $value);
            }
            $users = $db->getUserNicknameByArr($users);
            foreach($list as $key => $value){
                $list[$key]['from_nickname'] = $users[$value['from_user_ybid']];
                $list[$key]['to_nickname'] = $users[$value['to_user_ybid']];
            }
        }

        return $list;
    }

/**
 * 获取话题详情，包括前五条评论
 *
 * @param  int $topic_id 
 * @return array           
 */
    public static function getTopicInfo($topic_id){

        $db = new Data_Topic();
        $topic = $db->getTopicInfo($topic_id, true);
        $topic['comment'] = $db->getCommentList($topic_id, 0);
        if(!$topic['comment']){
            $topic['comment'] = array();
        }

        $db->setHotTopic($topic_id);

        if(count($topic['comment']) != 0){
            $db = new Data_User();
            $users = array();
            foreach($topic['comment'] as $key => $value){
                $users[] = $value['from_user_ybid'];
            }
            $users = array_unique($users);
            foreach($users as $key => $value){
                $users[$key] = array('user_ybid' => $value);
            }
            $users = $db->getUserNicknameByArr($users);
            foreach($topic['comment'] as $key => $value){
                $topic['comment'][$key]['nickname'] = $users[$value['from_user_ybid']];
            }
        }

        return $topic;
    }


/**
 * 发起话题
 *
 * @param string $title   话题标题
 * @param string $content 话题内容
 */
    public static function addTopic($title, $content){
        if(empty($title) || empty($content)){
            return 1;//数据为空
        }

        $db = new Data_Topic();
        $ret = $db->addTopic($_SESSION['yb_user_info']['yb_userid'], $title, $content);

        if(!$ret){
            return 2;//发布话题失败
        }

        $db = new Data_User();
        $ret = $db->addUserPoint($_SESSION['yb_user_info']['yb_userid'], 1, 2);
        if(!$ret){
            return 3;//添加master值失败
        }

        return true;
    }

/**
 * 为话题或者评论点赞
 *
 * @param int $type  类型  1.话题 2.评论
 * @param int $to_id 对象id
 */
    public static function addPraise($type, $to_id){
        if(empty($type) || empty($to_id)){
            return 1;//数据为空
        }

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db_Topic = new Data_Topic();
        $db_User = new Data_User();

        if($type == 1){
            $topic = $db_Topic->getTopicInfo($to_id);
            if(!$topic){
                return 2;//error 话题不存在
            }
            $author = $topic['user_ybid'];//话题发起者
        }else if($type == 2){
            $comment = $db_Topic->getCommentInfo($to_id);
            if(!$comment){
                return 3;//error 评论不存在
            }
            $author = $comment['from_user_ybid'];
        }else{
            $user = $db_User->getUserInfo($obj_id);
            if(!$user){
                return 4;//error 用户不存在
            }
            $author = $obj_id;
        }

        $isPraised = $db_Topic->isPraised($user_ybid, $to_id, $type);
        if($isPraised){
            $ret = $db_Topic->deletePraise($user_ybid, $to_id, $type);
            if(!$ret){
                return 5;//error 取消点赞失败
            }
            $result = 9;
            $point = -1;
        }else{
            $ret = $db_Topic->addPraise($user_ybid, $to_id, $type);
            if(!$ret){
                return 6;//error 点赞失败
            }
            $result = 8;
            $point = 1;
        }

        $ret = $db_User->addUserPoint($author, $point, 2);
        if(!$ret){
            return 7;//error master值改变失败
        }

        return $result;
    }

/**
 * 回复话题或者回复评论
 *
 * @param int $topic_id     [description]
 * @param string $content      [description]
 * @param int $to_user_ybid [description]
 * @param int $comment_id   [description]
 */
    public static function addComment($topic_id, $content, $to_user_ybid, $comment_id){
        if(empty($topic_id) || empty($content) || empty($to_user_ybid)){
            return 1;//error
        }

        if(!$comment_id){
            $comment_id = 0;
        }

        $db = new Data_Topic();

        $topic = $db->getTopicInfo($topic_id);
        if(!$topic){
            return 2;//error 话题不存在
        }

        if($comment_id == 0){
            if($topic['user_ybid'] != $to_user_ybid){
                return 3;//error 回复的不是话题发起者
            }
        }else{
            $comment = $db->getCommentInfo($comment_id);
            if(!$comment){
                return 4;//error 评论不存在
            }
        }

        $ret = $db->addComment($topic_id, $_SESSION['yb_user_info']['yb_userid'], $content, $to_user_ybid, $comment_id);
        if(!$ret){
            return 5;//error 添加评论失败
        }

        //$topic = $db->getTopicInfo($topic_id);

        // $db = new Data_User();
        // $ret = $db->addUserPoint($_SESSION['yb_user_info']['yb_userid'], 1, 1);
        // if(!$ret){
        //     return 3;
        // }

        //$ret = $db->addUserPoint($topic['user_ybid'], 1, 1);


        return true;
    }
}
?>