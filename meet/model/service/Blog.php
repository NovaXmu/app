<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Blog.php
*   description:      Service for Blog.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Service_Blog{
    function __construct(){}

/**
 * 获取部落列表
 *
 * @param  boolean $isMine 是否是我加入的 false 所有部落 true 是我创建的部落
 * @param  integer $type   [description]
 * @return [type]          [description]
 */
    public static function getBlogList($isMine = false){

        $db = new Data_Blog();
        $list = $db->getBlogList($isMine);

        if(!$list){
            return array();
        }

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        foreach($list as $key => $value){
            $list[$key]['isHost'] = false;
            if($value['user_ybid'] == $user_ybid){
                $list[$key]['isHost'] = true;
            }else{
                $list[$key]['isJoined'] = $db->isJoinedBlog($user_ybid, $value['id']);
            }
        }  

        return $list;
    }

    public static function getBlogMemberList($blog_id){

        //获取易班账号和部落内部积分
        $db = new Data_Blog();

        $blog = $db->getBlogInfo($blog_id);
        $data['title'] = $blog['name'];
        $data['blog_id'] = $blog['id'];

        $data['list'] = $db->getBlogMemberList($blog_id);

        if(!$data['list']){
            return array();
        }

        //获取昵称
        $db = new Data_User();
        $userNickname = $db->getUserNicknameByArr($data['list']);
        if(!$userNickname){
            $userNickname = array();
        }
        foreach($data['list'] as $key => $value){
            $data['list'][$key]['nickname'] = $userNickname[$value['user_ybid']];
        }

        return $data;
    }

    public static function getBlogActivity($blog_id, $isHost = false, $isJoined = false){

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_Blog();

        $blog = $db->getBlogInfo($blog_id);

        $data['title'] = $blog['name'];

        $data['list'] = $db->getBlogActivity($blog_id, $user_ybid, $isHost, $isJoined);
        if(!$data['list']){
            $data['list'] = array();
        }

        return $data;
    }

    public static function getActivityMemberList($activity_id){

        $db = new Data_Blog();

        //获取活动信息
        $activity = $db->getActivityInfo($activity_id);
        $data['title'] = $activity['title'];
        $data['blog_id'] = $activity['blog_id'];

        //获取易班账号
        $data['list'] = $db->getActivityMemberList($activity_id);
        if(!$data['list']){
            return array();
        }

        //获取昵称
        $db = new Data_User();
        $userNickname = $db->getUserNicknameByArr($data['list']);
        if(!$userNickname){
            $userNickname = array();
        }
        foreach($data['list'] as $key => $value){
            $data['list'][$key]['nickname'] = $userNickname[$value['user_ybid']];
        }

        return $data;
    }

    public static function addBlog($data){

        if(is_bool($data)){
            return 1;
        }

        if(!isset($data['name']) || !isset($data['introduction'])){
           return 2;
        }


        if(strlen($data['introduction']) > 50){
            return 3;
        }

        $db = new Data_Blog();
        $result = $db->addBlog($_SESSION['yb_user_info']['yb_userid'], $data['name'], $data['introduction'], 2);
        if(!$result){
            return 4;
        }

        $db = new Data_User();
        $result = $db->addUserPoint($_SESSION['yb_user_info']['yb_userid'], 10, 1);
        if(!$result){
            return 5;
        }

        return true;
    }

    public static function joinBlog($blog_id){

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_Blog();

        $ret = $db->isHostBlog($user_ybid, $blog_id);
        if($ret){
            return 1;//是部落的创建人
        }

        $ret = $db->isJoinedBlog($user_ybid, $blog_id);
        if($ret){
            return 2;//已经加入该部落了
        }

        $ret = $db->addBlogMember($user_ybid, $blog_id);
        if(!$ret){
            return 3;//添加成员失败
        }

        //新成员加入，部落积分+1
        $ret = $db->addBlogPoint($blog_id, 1);
        if(!$ret){
            return 4;//部落积分增加积分失败
        }

        $db = new Data_User();
        $ret = $db->addUserPoint($user_ybid, 1, 1);
        if(!$ret){
            return 5;//用户的interpers值增加失败
        }

        return true;
    }

    public static function quitBlog($blog_id){

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_Blog();

        $ret = $db->isHostBlog($user_ybid, $blog_id);
        if($ret){
            return 1;//是部落的创建人
        }

        $ret = $db->isJoinedBlog($user_ybid, $blog_id);
        if(!$ret){
            return 2;//没有加入该部落
        }

        $count = $db->deleteBlogMember($user_ybid, $blog_id);
        if(is_bool($count)){
            return 3;//删除成员失败
        }

        //成员退出部落，部落积分减去 1+$count
        $ret = $db->addBlogPoint($blog_id, (-1 - $count));
        if(!$ret){
            return 4;//减少积分失败
        }

        $db = new Data_User();
        $ret = $db->addUserPoint($user_ybid, (-1 - $count), 1);
        if(!$ret){
            return 5;
        }

        return true;
    }
    
}
?>