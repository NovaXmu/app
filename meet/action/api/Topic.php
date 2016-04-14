<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Topic.php
*   description:      Action_Api for Topic.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Action_Api_Topic extends Action_Base{
    function __construct(){}

    public function run(){
        $m = Library_Share::getRequest('m');
        if(is_bool($m) && !$m){
            $return = array('errno' => '1', 'errmsg' => '参数不对');
        }else{
            switch($m){
            case 'getMoreTopic'://test pass
                $return = $this->getMoreTopic();
                break;
            case 'getMoreComment':
                $return = $this->getMoreComment();
                break;
            case 'getCommentDetail':
                $return = $this->getCommentDetail();
                break;
            case 'addTopic'://test pass
                $return = $this->addTopic();
                break;
            case 'addPraise'://test pass
                $return = $this->addPraise();
                break;
            case 'addComment':
                $return = $this->addComment();
                break;
            }
        }

        if(isset($return['errno']) && $return['errno'] == 1){
            $log = Library_Share::getLog(true, $return['errmsg']);
        }else{
            $log = Library_Share::getLog(true);
        }
        $log = json_encode($log, JSON_UNESCAPED_UNICODE);
        Vera_Log::addLog('api', $log);

        echo json_encode($return, JSON_UNESCAPED_UNICODE);
        return true;
    }

/**
 * 获取更多话题5条
 *
 * @return array 
 */
    private function getMoreTopic(){
        $index = Library_Share::getRequest('index', Library_Share::INT_DATA);
        if(is_bool($index)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Topic::getMoreTopic($index);

        if(is_int($result)){
            switch($result){
                case '1':
                    $ret = array('errno' => '1', 'errmsg' => 'index 不能为空');
                    break;
                case '2':
                    $ret = array('errno' => '1', 'errmsg' => '获取更多话题失败');
                    break;
            }
            return $ret;
        }

        return $result;
    }

    private function getMoreComment(){
        $index = Library_Share::getRequest('index', Library_Share::INT_DATA);
        $topic_id = Library_Share::getRequest('topic_id', Library_Share::INT_DATA);
        if(is_bool($index) || is_bool($topic_id)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Topic::getMoreComment($topic_id, $index);

        if(is_int($result)){
            switch($result){
                case '1':
                    $ret = array('errno' => '1', 'errmsg' => 'index 不能为空');
                    break;
                case '2':
                    $ret = array('errno' => '1', 'errmsg' => '获取更多评论失败');
                    break;
            }
            return $ret;
        }

        return $result;
    }

    private function getCommentDetail(){
        $comment_id = Library_Share::getRequest('comment_id', Library_Share::INT_DATA);
        if(is_bool($comment_id)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Topic::getCommentDetail($comment_id);

        if(is_int($result)){
            switch($result){
                case '1':
                    $ret = array('errno' => '1', 'errmsg' => '评论id 不能为空');
                    break;
                case '2':
                    $ret = array('errno' => '1', 'errmsg' => '获取更多回复失败');
                    break;
            }
            return $ret;
        }

        return $result;
    }

/**
 * 发布话题
 *
 */
    private function addTopic(){
        $title = Library_Share::getRequest('title');
        $content = Library_Share::getRequest('content');
        if(is_bool($title) || is_bool($content)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Topic::addTopic($title, $content);

        if(!is_bool($result)){
            switch($result){
                case '1':
                    $ret = array('errno' => '1', 'errmsg' => '标题、内容不能为空');
                    break;
                case '2':
                    $ret = array('errno' => '1', 'errmsg' => '发布话题失败，请稍后再试');
                    break;
                case '3':
                    $ret = array('errno' => '1', 'errmsg' => 'master值增加失败');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0', 'errmsg' => 'ok');
        return $ret;
    }

/**
 * 点赞
 *
 */
    private function addPraise(){
        $type = Library_Share::getRequest('type', Library_Share::INT_DATA);//1.话题 2.评论
        $to_id = Library_Share::getRequest('to_id', Library_Share::INT_DATA);
        if(is_bool($type) || is_bool($to_id)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Topic::addPraise($type, $to_id);

        if(is_int($result) && $result < 8){
            switch($result){
                case '1':
                    $ret = array('errno' => '1', 'errmsg' => '类型、对象id不能为空');
                    break;
                case '2':
                    $ret = array('errno' => '1', 'errmsg' => '话题不存在');
                    break;
                case '3':
                    $ret = array('errno' => '1', 'errmsg' => '评论不存在');
                    break;
                case '4':
                    $ret = array('errno' => '1', 'errmsg' => '用户不存在');
                    break;
                case '5':
                    $ret = array('errno' => '1', 'errmsg' => '取消点赞失败');
                    break;
                case '6':
                    $ret = array('errno' => '1', 'errmsg' => '点赞失败');
                    break;
                case '7':
                    $ret = array('errno' => '1', 'errmsg' => 'master值增加失败');
                    break;
            }
            return $ret;
        }
        if($result == 8){
            $ret = array('errno' => '0', 'errmsg' => '点赞成功');
        }else{
            $ret = array('errno' => '0', 'errmsg' => '取消点赞成功');
        }
        return $ret;
    }

/**
 * 发布评论
 *
 */
    private function addComment(){
        $topic_id = Library_Share::getRequest('topic_id', Library_Share::INT_DATA);//1.话题 2.评论
        $content = Library_Share::getRequest('content');
        $to_user_ybid = Library_Share::getRequest('to_id', Library_Share::INT_DATA);
        $comment_id = Library_Share::getRequest('comment_id', Library_Share::INT_DATA);

        if(is_bool($topic_id) || is_bool($content) || is_bool($to_user_ybid)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Topic::addComment($topic_id, $content, $to_user_ybid, $comment_id);


        if(!is_bool($result)){
            switch($result){
                case '1':
                    $ret = array('errno' => '1', 'errmsg' => '内容不能为空');
                    break;
                case '2':
                    $ret = array('errno' => '1', 'errmsg' => '话题不存在');
                    break;
                case '3':
                    $ret = array('errno' => '1', 'errmsg' => '回复的不是话题发起者');
                    break;
                case '4':
                    $ret = array('errno' => '1', 'errmsg' => '评论不存在');
                    break;
                case '5':
                    $ret = array('errno' => '1', 'errmsg' => '添加评论失败');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0', 'errmsg' => 'ok');
        return $ret;
    }
}
?>