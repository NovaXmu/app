<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Message.php
*   description:      Service for Message.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/

class Service_Message{
    function __construct(){}

/**
 * 获取信息列表(话题、PK、私信)
 *
 * @return array             
 */
    public static function getMessageMenu(){
        $data = array();
        //1.获取未读话题消息计数
        $db_Topic = new Data_Topic();
        $data['topic'] = $db_Topic->getMyUnreadCommentCount($_SESSION['yb_user_info']['yb_userid']);
        //2.获取未读PK消息计数和未读悄悄话计数
        $db_Message = new Data_Message();
        $data['pk'] = $db_Message::getMessageMenu($_SESSION['yb_user_info']['yb_userid'], 4);
        $data['pk'] = $data['pk'][0]['count'];
        $data['friend'] = $db_Message::getMessageMenu($_SESSION['yb_user_info']['yb_userid'], 1);
        if(count($data['friend']) != 0){
            $db_User = new Data_User();
            $nicknameArr = $db_User->getUserNicknameByArr($data['friend']);
            foreach($data['friend'] as $key => $value){
                $data['friend'][$key]['nickname'] = $nicknameArr[$value['user_ybid']];
            }
        }

        return $data;
    }

/**
* 获取信息内容
* @return array
*/
    public static function getMessage($type, $obj_id, $index = 0, $setRead = 1){

        $data = array(
            'ret' => array('errno' => '0', 'errmsg' => 'ok'),
            'title' => 'xxx',
            'return_id' => '',
            'to_id' => '',
            'type' => $type,
            'list' => array()
            );

        if(is_int($type) && $type < 5){
            switch($type){
                case 1://获取悄悄话
                    //对于悄悄话来说 obj_id是指对方， 即from_user_ybid
                    $db_User = new Data_User();
                    $user = $db_User->getUserInfo($obj_id);
                    $data['to_id'] = $user['ybid'];
                    $data['title'] = $user['nickname'];
                    break;
                case 2://获取活动消息
                    $db_Blog = new Data_Blog();
                    $activity = $db_Blog->getActivityInfo($obj_id);
                    $data['title'] = $activity['title'];
                    $data['return_id'] = $activity['blog_id'];
                    $data['to_id'] = $activity['id'];

                    $user_ybid = $_SESSION['yb_user_info']['yb_userid'];
                    $ret = $db_Blog->isJoinedActivity($user_ybid, $obj_id);
                    if(!$ret){
                        $data['ret'] = array('errno' => '1', 'errmsg' => '您没有参加该活动不能查看消息');
                        return $data;
                    }
                    break;
                case 3://获取部落消息
                    $db_Blog = new Data_Blog();
                    $blog = $db_Blog->getBlogInfo($obj_id);
                    $data['title'] = $blog['name'];
                    $data['to_id'] = $blog['id'];

                    $user_ybid = $_SESSION['yb_user_info']['yb_userid'];
                    $ret = $db_Blog->isJoinedBlog($user_ybid, $obj_id);
                    if(!$ret){
                        $data['ret'] = array('errno' => '1', 'errmsg' => '您没有参加该部落不能查看消息');
                        return $data;
                    }
                    break;
                case 4://获取PK消息
                    //对于PK来说 obj_id是指自己
                    $data['title'] = 'PK';
                    break;
                default:
                    return false;
                    break;
            }
            $db = new Data_Message();
            $data['list'] = $db->getMessage($type, $obj_id, $index);
            if($index == 0 && $setRead == 1){
                if($type == 1)//设置悄悄话已读
                    $db->setMessageReaded($type, $_SESSION['yb_user_info']['yb_userid'], $obj_id);
                else if($type == 4)//设置PK消息已读
                    $db->setMessageReaded($type, $obj_id);
            }
            if($type == 4 && count($data['list']) != 0){
                $db_User = new Data_User();
                $arr = array();
                foreach($data['list'] as $key => $value)
                    $arr[] = array('user_ybid' => $value['from_user_ybid']);
                $arr = $db_User->getUserNicknameByArr($arr);
                foreach($data['list'] as $key => $value)
                    $data['list'][$key]['title'] = $arr[$value['from_user_ybid']];
            }
        }else if($type == 5){//获取话题消息
            //对于话题消息，obj_id是指自己
            $db = new Data_Topic();
            $data['title'] = '话题消息';
            $data['list'] = $db->getMyComment($obj_id, $index);
            if($index == 0 && $setRead == 1){
                $db->setCommentReaded($obj_id);
            }
        }

        if($type == 1 || $type == 2 || $type == 3)
            krsort($data['list']);

        if($type == 4){
            foreach($data['list'] as $key => $value){
                switch($value['content']){
                    case 1:
                        $data['list'][$key]['content'] = '平局~ 下次再战';
                        break;
                    case 2:
                        $data['list'][$key]['content'] = '被欺负了 T~T ';
                        break;
                    case 3:
                        $data['list'][$key]['content'] = 'haha~ 小样儿 我赢啦~';
                        break;
                }
            }
        }

        return $data;
    }

/**
*   发送消息
*/
    public static function sendMessage($data, $type){

        if(!isset($data['to']) || !isset($data['content'])){
            return 1;//'缺失参数';
        }

        if(empty($data['content'])){
            return 2;//'消息内容不能为空';
        }

        if(count($data['content']) > 50){
            return 3;//'消息内容长度不为超过50';
        }

        if($type != 1 && $type != 2 && $type != 3 && $type !=4){
            return 4;//对象类型错误
        }

        $db = new Data_Blog();
        if($type == 2){
            $ret = $db->isJoinedActivity($_SESSION['yb_user_info']['yb_userid'], $data['to']);
            if(!$ret){
                return 5;
            }
        }else if($type == 3){
            $ret = $db->isJoinedBlog($_SESSION['yb_user_info']['yb_userid'], $data['to']);
            if(!$ret){
                return 6;
            }
        }

        $ret = Data_Message::addMessage($_SESSION['yb_user_info']['yb_userid'], $data['to'], $type, $data['content']);
        if(!$ret){
            return 7;
        }

        //要不要加用户的interpersonal值？
        

        return true;
    }
}
?>