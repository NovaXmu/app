<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Blog.php
*   description:      Data for Blog.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Data_Blog{
    function __construct(){}

    public function addBlog($user_ybid, $name, $introduction, $type){

        $data = array(
            'user_ybid' => $user_ybid,
            'name' => $name,
            'introduction' => $introduction,
            'type' => $type,
            'time' => date('Y-m-d H:i:s'),
            'isPassed' => 1
            );

        if($type == 2){
            $data['point'] = 1;
        }else{
            $data['point'] = 0;
        }

        $db = Vera_Database::getInstance();
        $ret = $db->insert('meet_Blog', $data);

        if(!$ret){
            return false;
        }

        if($type == 2){
            $ret = $db->select('meet_Blog', 'id', $data);
            if(!$ret){
                //记错误日志
            }

            //创建部落的人在部落内的积分初始化为10
            $ret = $this->addBlogMember($user_ybid, $ret[0]['id'], 10);
            if(!$ret){
                //记错误日志
            }
        }

        return true;
    }

    public function addBlogMember($user_ybid, $blog_id, $point = 0){

        $data = array(
            'user_ybid' => $user_ybid,
            'blog_id' => $blog_id,
            'point' => $point,
            'time' => date('Y-m-d H:i:s')
            );

        $db = Vera_Database::getInstance();
        $ret = $db->insert('meet_BlogMember', $data);

        if(!$ret){
            return false;
        }

        return true;
    }

    public function deleteBlogMember($user_ybid, $blog_id){

        $db = Vera_Database::getInstance();

        //1.删除成员在该部落中参加的未到报名截止日期的活动，并且记录删除记录的数量
        
        $conds = "(blog_id = $blog_id) AND (deadline >=" . '"'. date('Y-m-d') . '")';
        $activityList = $db->select('meet_Activity', 'id', $conds);

        if(is_bool($activityList)){
            return false;
        }

        $count = count($activityList);
        if($count != 0){//1.2用户参与了该部落正在进行的活动

            //构造条件
            $idList = array();
            foreach($activityList as $key => $value){
                $idList[] = $value['id'];
            }
            $conds = "(user_ybid = $user_ybid) AND (activity_id IN (" . implode(',', $idList) . "))";

            $ret = $db->delete('meet_ActivityMember', $conds);
            if(!$ret){
                return false;
            }
        }

        //2.删除部落成员记录
        $conds = array(
            'user_ybid' => $user_ybid,
            'blog_id' => $blog_id
            );

        $ret = $db->delete('meet_BlogMember', $conds);

        if(!$ret){
            return false;
        }

        return $count;
    }

    public function addBlogPoint($blog_id, $add){

        $conds = array('id' => $blog_id);
        
        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_Blog', 'point', $conds);
        if(!$ret){
            return false;
        }

        $data = array('point' => ($ret[0]['point'] + $add) );

        $ret = $db->update('meet_Blog', $data, $conds);
        if(!$ret){
            return false;
        }

        return true;
    }

    public function getBlogInfo($blog_id){
        $conds = array('id' => $blog_id);
        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_Blog', '*', $conds);
        if(!isset($ret[0]['id'])){
            return false;
        }

        return $ret[0];
    }

    public function getBlogList($isMine = false){

        $appends = 'order by point desc, time desc';
        $conds = NULL;

        $db = Vera_Database::getInstance();

        if($isMine){//查找该用户创建的部落
            $conds = array('user_ybid' => $_SESSION['yb_user_info']['yb_userid']);
            $myBlog = $db->select('meet_BlogMember', 'blog_id', $conds);
            $idList = array();
            foreach($myBlog as $key => $value){
                $idList[] = $value['blog_id'];
            }
            $conds = '(id IN (' . implode(',', $idList). '))';
        }

        $ret = $db->select('meet_Blog', '*', $conds, NULL, $appends);

        if(!$ret){
            return false;
        }

        //部落成员人数
        foreach($ret as $key => $value){
            $ret[$key]['count'] = $db->selectCount('meet_BlogMember', array('blog_id' => $value['id']) );
        }

        return $ret;
    }

    public function isJoinedBlog($user_ybid, $blog_id){
        $conds = array(
            'user_ybid' => $user_ybid, 
            'blog_id' => $blog_id
            );

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_BlogMember', '*', $conds);
        if(isset($ret[0]['id'])){
            return true;
        }

        return false;
    }

    public function isHostBlog($user_ybid, $blog_id){
        $conds = array(
            'user_ybid' => $user_ybid,
            'id' => $blog_id
            );

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_Blog', '*', $conds);

        if(isset($ret[0]['id'])){
            return true;
        }

        return false;
    }

    public function getBlogMemberList($blog_id){

        $conds = array('blog_id' => $blog_id);
        $appends = 'order by point desc';

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_BlogMember', 'user_ybid, point', $conds, NULL, $appends);

        if(!$ret){
            return false;
        }

        return $ret;
    }

    public function addActivity($user_ybid, $data){

        $data['user_ybid'] = $user_ybid;
        $data['time'] = date('Y-m-d H:i:s');

        $db = Vera_Database::getInstance();
        $ret = $db->insert('meet_Activity', $data);
        if(!$ret){
            return false;
        }

        $ret = $db->select('meet_Activity', '*', $data);
        if(!$ret){
            //记Log
        }

        $ret = $this->addActivityMember($user_ybid, $ret[0]['id']);
        if(!$ret){
            //记Log
        }

        return true;
    }

    public function getActivityMemberList($activity_id){
        $conds = array('activity_id' => $activity_id);
        $appends = 'order by time asc';

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_ActivityMember', 'user_ybid', $conds, NULL, $appends);
        if(!$ret){
            return false;
        }

        return $ret;
    }

    public function deleteActivity($activity_id){

        $db = Vera_Database::getInstance();

        //1.删除有关该活动的成员记录
        $conds = array('activity_id' => $activity_id);
        $ret = $db->delete('meet_ActivityMember', $conds);
        if(!$ret){
            return false;
        }

        //2.删除活动记录
        $conds = array('id' => $activity_id);
        $ret = $db->delete('meet_Activity', $conds);
        if(!$ret){
            return false;
        }

        return true;
    }

    public function getActivityInfo($activity_id, $fields = '*'){

        $conds = array('id' => $activity_id);

        $db = Vera_Database::getInstance();

        $ret = $db->select('meet_Activity', $fields, $conds);

        if(!$ret){
            return false;
        }

        return $ret[0];
    }

    public function getBlogActivity($blog_id = NULL, $user_ybid = NULL, $isHost = false, $isJoined = false){

        $conds = "(1 = 1) ";

        if($blog_id != NULL){
            $conds = "(blog_id = $blog_id) ";
        }
            
        $db = Vera_Database::getInstance();

        if($isHost){
            $conds .= "AND (user_ybid = $user_ybid)";
        }else if($isJoined){
            $tempConds = array('user_ybid' => $user_ybid);
            $ret = $db->select('meet_ActivityMember', 'activity_id', $tempConds);
            if(!$ret || count($ret) == 0){//没有取到数据
                return false;
            }
            $idList = array();
            foreach($ret as $key => $value){
                $idList[] = $value['activity_id'];
            }

            $conds .= 'AND (id IN (' . implode(',', $idList) . '))';
        }

        $appends = 'order by time desc';

        $ret = $db->select('meet_Activity', '*', $conds, NULL, $appends);

        if(!$ret){
            return false;
        }

        if(!isset($ret[0]['id'])){
            return array();
        }

        foreach($ret as $key => $value){
            if($value['user_ybid'] == $user_ybid){
                $ret[$key]['isHost'] = true;
            }else{
                $ret[$key]['isHost'] = false;
                $ret[$key]['isJoined'] = $this->isJoinedActivity($user_ybid, $value['id']);
            }
        }

        foreach($ret as $key => $value){
            $ret[$key]['count'] = $this->getActivityMemberCount($value['id']);
        }

        return $ret;
    }

    public function getActivityMemberCount($activity_id){

        $db = Vera_Database::getInstance();
        $ret = $db->selectCount('meet_ActivityMember', array('activity_id' => $activity_id));

        if(!$ret){
            //记Log
            return 0;
        }

        return $ret;
    }

    public function addActivityMember($user_ybid, $activity_id){

        $data = array(
            'user_ybid' => $user_ybid,
            'activity_id' => $activity_id,
            'time' => date('Y-m-d H:i:s')
            );

        $db = Vera_Database::getInstance();
        $ret = $db->insert('meet_ActivityMember', $data);

        if(!$ret){
            return false;
        }

        return true;
    }

    public function isJoinedActivity($user_ybid, $activity_id){

        $conds = array(
            'user_ybid' => $user_ybid,
            'activity_id' => $activity_id
            );

        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_ActivityMember', '*', $conds);

        if(!$ret){
            return false;
        }

        if(!isset($ret[0]['id'])){
            return false;
        }

        return true;
    }

    public function deleteActivityMember($user_ybid, $activity_id){

        $conds = array(
            'user_ybid' => $user_ybid,
            'activity_id' => $activity_id
            );

        $db = Vera_Database::getInstance();
        $ret = $db->delete('meet_ActivityMember', $conds);

        if(!$ret){
            return false;
        }

        return true;
    }

    public function addUserInBlogPoint($user_ybid, $blog_id, $add){
        $conds = array(
            'user_ybid' => $user_ybid,
            'blog_id' => $blog_id
            );
        
        $db = Vera_Database::getInstance();
        $ret = $db->select('meet_BlogMember', 'point', $conds);
        if(!$ret){
            return false;
        }

        $data = array('point' => ($ret[0]['point'] + $add) );

        $ret = $db->update('meet_BlogMember', $data, $conds);
        if(!$ret){
            return false;
        }

        return true;
    }

    public function updateActivity($data){
        $conds = array('id' => $data['activity_id']);
        $row = array(
            'title' => $data['title'],
            'content' => $data['content'],
            'deadline' => $data['deadline'],
            'limitNum' => $data['limitNum']
            );

        $db = Vera_Database::getInstance();
        $ret = $db->update('meet_Activity', $row, $conds);

        if(!$ret){
            return false;
        }

        return true;
    }

}
?>