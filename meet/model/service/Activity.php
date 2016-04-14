<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Blog.php
*   description:      Service for Activity.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Service_Activity{
    function __construct(){}

    public static function joinActivity($activity_id){
        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_Blog();

        $activity = $db->getActivityInfo($activity_id);
        if(!isset($activity['blog_id'])){
            return 1;//不存在该活动
        }

        $blog_id = $activity['blog_id'];

        $ret = $db->isJoinedBlog($user_ybid, $blog_id);
        if(!$ret){
            return 2;//没有加入该部落不可参加该活动
        }

        if($activity['deadline'] < date('Y-m-d')){
            return 3;//已经过了报名截止时间
        }

        if($activity['limitNum'] > 0){
            $count = $db->getActivityMemberCount($activity_id);
            if($count >= $activity['limitNum']){
                return 4;//报名人数已满
            }
        }

        $ret = $db->isJoinedActivity($user_ybid, $activity_id);
        if($ret){
            return 5;//已经加入了该活动
        }

        $ret = $db->addActivityMember($user_ybid, $activity_id);
        if(!$ret){
            return 6;//加入该活动失败
        }

        $ret = $db->addBlogPoint($blog_id, 1);
        if(!$ret){
            return 7;//添加部落积分失败
        }

        //增加发起人在部落内的积分
        $ret = $db->addUserInBlogPoint($activity['user_ybid'], $blog_id, 1);
        if($ret){
            //记录错误日志
        }

        //增加用户在部落内的积分
        $ret = $db->addUserInBlogPoint($user_ybid, $blog_id, 1);
        if(!$ret){
            return 8;
        } 

        $db = new Data_User();

        //增加发起人的interper值
        $ret = $db->addUserPoint($activity['user_ybid'], 1, 1);
        if($ret){
            //记录错误日志
        }

        //增加用户interpersonal值
        $ret = $db->addUserPoint($user_ybid, 1, 1);
        if(!$ret){
            return 9;//增加用户interpersonal值失败
        }

        return true;
    }

    public static function quitActivity($activity_id){

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_Blog();

        $activity = $db->getActivityInfo($activity_id);
        if(!isset($activity['blog_id'])){
            return 1;//不存在该活动
        }

        if($activity['user_ybid'] == $user_ybid){
            return 2;//活动发起人不能退出活动
        }

        $blog_id = $activity['blog_id'];

        if($activity['deadline'] < date('Y-m-d')){
            return 3;//已经过了报名截止时间,不能退出活动
        }

        $ret = $db->isJoinedActivity($user_ybid, $activity_id);
        if(!$ret){
            return 4;//没有加入该活动
        }

        $ret = $db->deleteActivityMember($user_ybid, $activity_id);
        if(!$ret){
            return 5;//退出该活动失败
        }

        $ret = $db->addBlogPoint($blog_id, -1);
        if(!$ret){
            return 6;//减少部落积分失败
        }

        //减少发起人在部落内的积分
        $ret = $db->addUserInBlogPoint($activity['user_ybid'], $blog_id, -1);
        if(!$ret){
            //var_dump('error: 减少发起人在部落内的积分失败');
        }

        //减少用户在部落内的积分
        $ret = $db->addUserInBlogPoint($user_ybid, $blog_id, -1);
        if(!$ret){
            return 7;//减少用户在部落内的积分失败
        } 

        $db = new Data_User();

        //减少发起人的interpersonal值
        $ret = $db->addUserPoint($activity['user_ybid'], -1, 1);
        if(!$ret){
            //var_dump('error: 减少发起人的interper值失败');
        }

        //减少用户interpersonal值
        $ret = $db->addUserPoint($user_ybid, -1, 1);
        if(!$ret){
            return 8;//减少用户interpersonal值失败
        }

        return true;
    }

    public static function addActivity($data){

        if(!isset($data['blog_id']) || empty($data['blog_id']) || !isset($data['title']) || empty($data['title']) || !isset($data['limitNum']) || empty($data['limitNum']) || !isset($data['deadline']) || empty($data['deadline']) || !isset($data['content']) || empty($data['content']) ){
            return 1;//参数错误
        }

        if($data['deadline'] < date('Y-m-d')){
            return 2;//截止时间不能再当前时间之前
        }

        if(count($data['content']) > 50){
            return 3;//活动内容过长
        }

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_Blog();

        $ret = $db->isJoinedBlog($user_ybid, $data['blog_id']);
        if(!$ret){
            return 4;//没有加入该部落不可发起活动
        }

        //新增活动
        $ret = $db->addActivity($user_ybid, $data);

        if(!$ret){
            return 5;
        }

        $ret = $db->addBlogPoint($data['blog_id'], 2);
        if(!$ret){
            return 6;//添加部落积分失败
        }

        $ret = $db->addUserInBlogPoint($user_ybid, $data['blog_id'], 2);
        if(!$ret){
            return 7;//添加用户在部落内的积分失败
        } 

        $db = new Data_User();
        $ret = $db->addUserPoint($user_ybid, 2, 1);
        if(!$ret){
            return 8;//增加用户interpersonal值失败
        }

        return true;
    }

    public static function updateActivity($data){
        if(!isset($data['blog_id']) || empty($data['blog_id']) || !isset($data['activity_id']) || empty($data['activity_id']) || !isset($data['title']) || empty($data['title']) || !isset($data['limitNum']) || empty($data['limitNum']) || !isset($data['deadline']) || empty($data['deadline']) || !isset($data['content']) || empty($data['content']) ){
            return 1;//参数错误
        }

        $db = new Data_Blog();

        $activity = $db->getActivityInfo($data['activity_id']);

        if(!isset($activity['blog_id']) || $activity['blog_id'] != $data['blog_id']){
            return 2;//不存在该活动
        }

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        if($activity['user_ybid'] != $user_ybid){
            return 3;//不是活动发起人
        }

        if($activity['deadline'] < date('Y-m-d')){
            return 4;//已经过了报名截止时间,不能修改活动
        }

        //检查更新内容
        if($data['deadline'] < date('Y-m-d')){
            return 5;//截止时间不能再当前时间之前
        }

        if(count($data['content']) > 50){
            return 6;//活动内容过长
        }

        $blog_id = $activity['blog_id'];

        //修改活动
        $ret = $db->updateActivity($data);

        if(!$ret){
            return 7;
        }

        return true;
    }

    public static function deleteActivity($activity_id){

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_Blog();


        $activity = $db->getActivityInfo($activity_id);
        if(!isset($activity['blog_id'])){
            return 1;//不存在该活动
        }

        $blog_id = $activity['blog_id'];

        $ret = $db->isJoinedBlog($user_ybid, $blog_id);
        if(!$ret){
            return 2;
        }

        if($activity['user_ybid'] != $user_ybid){
            return 3;//不是活动发起人
        }

        if($activity['deadline'] <= date('Y-m-d')){
            return 4;//已经过了报名截止时间
        }

        //获取参与该活动的成员列表
        $memberList = $db->getActivityMemberList($activity_id);
        if(is_bool($memberList)){
            return 5;
        }

        $ret = $db->deleteActivity($activity_id);
        if(!$ret){
            return 6;
        }

        $count = count($memberList);

        //减少部落积分
        $ret = $db->addBlogPoint($blog_id, (-1-$count));
        if(!$ret){
            return 7;//减少部落积分失败
        }

        //减少参与用户在部落内的积分和interpersonal值
        $dbUser = new Data_User();
        foreach($memberList as $key => $value){
            $db->addUserInBlogPoint($value['user_ybid'], $blog_id, -1);
            $dbUser->addUserPoint($value['user_ybid'], -1, 1);
        }

        $ret = $db->addUserInBlogPoint($user_ybid, $blog_id, -$count);
        if(!$ret){
            return 8;
        }

        $ret = $dbUser->addUserPoint($user_ybid, -$count, 1);
        if(!$ret){
            return 7;
        }

        return true;
    }

    
}
?>