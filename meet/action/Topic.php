<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Topic.php
*   description:      Action for Topic.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Action_Topic extends Action_Base{
    function __construct($resource){
        parent::__construct($resource);
    }

    public function run(){
        $m = Library_Share::getRequest('m');
        if(is_bool($m)){
            return false;
        }
        switch($m){
            case 'index'://热门话题榜
                $this->_index();
                break;
            case 'topic'://话题详情
                $this->_topic();
                break;
        }
        return true;
    }

    private function _index(){
        $hotList = Service_Topic::getHotTopic();
        $timeList = Service_Topic::getMoreTopic(0, 2);

        // echo 'hot<br/>';
        // var_dump($hotList);
        // echo '<br/>';

        // echo 'list<br/>';
        // var_dump($timeList);
        // echo '<br/>';

        $view = new Vera_View(true);
        $view->assign('hot', $hotList);
        $view->assign('list', $timeList);
        $view->display('meet/TopicLists.tpl');
        return true;
    }

    private function _topic(){
        $topic_id = Library_Share::getRequest('topic_id', Library_Share::INT_DATA);
        if(is_bool($topic_id)){
            return false;
        }

        $topic = Service_Topic::getTopicInfo($topic_id);

        // echo 'topic<br/>';
        // var_dump($topic);
        // echo '<br/>';

        $view = new Vera_View(true);
        $view->assign('topic', $topic);
        $view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
        $view->display('meet/Topic.tpl');
        return true;
    }
}
?>