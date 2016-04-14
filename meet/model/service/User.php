<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             User.php
*   description:      Service for User.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/
class Service_User{
    function __construct(){}

/**
 * 获取用户已经添加的标签，并对标签进行分类
 *
 * @param  int $user_ybid 
 * @return [type]            
 */
    public static function getUserLabelLog($user_ybid){
        //初始化标签分类
        $list = array(
                'food' => array('type' => 1, 'title' => '美食', 'list' => array()),
                'sport' => array('type' => 2, 'title' => '运动', 'list' => array()),
                'book' => array('type' => 3, 'title' => '书籍', 'list' => array()),
                'music' => array('type' => 4, 'title' => '音乐', 'list' => array()),
                'movie' => array('type' => 5, 'title' => '影视', 'list' => array())
                );

        $db = new Data_User();
        //获取用户已经添加过的标签的id的列表
        $result = $db->getUserLabelIdList($user_ybid);
        if(is_bool($result) || count($result) == 0){
            return $list;
        }

        //根据用户添加的标签id数组获取对应标签的信息
        $result = $db->getLabelInfoByArr($result);
        if(is_bool($result) || count($result) == 0){
            return $list;
        }

        //对获取的标签进行归类
        foreach($result as $key => $value){
            switch($value['type']){
                case 1:
                    $index = 'food';
                    break;
                case 2:
                    $index = 'sport';
                    break;
                case 3:
                    $index = 'book';
                    break;
                case 4:
                    $index = 'music';
                    break;
                case 5:
                    $index = 'movie';
                    break;
            }
            $list[$index]['list'][] = $value;
                    
        }

        return $list;
    }

/**
 * 用户需要更新标签是获取该分类下的所以标签并与用户已经添加过的标签进行标记
 *
 * @return array 
 */
    public static function getLabelList(){
        //获取标签大类
        $type = Library_Share::getRequest('type', Library_Share::INT_DATA);
        if(!$type){
            $type = 1;
        }

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_User();
        //获取用户已经添加过的标签
        $user = $db->getUserLabelIdList($user_ybid);
        if(is_bool($user)){
            $user = array();
        }

        $data = array();

        $data['type'] = $type;
        $data['title'] = 'xxx';
        switch($type){
            case 1:
                $data['title'] = '我爱的美食';
                break;
            case 2:
                $data['title'] = '我爱的运动';
                break;
            case 3:
                $data['title'] = '我爱的书籍';
                break;
            case 4:
                $data['title'] = '我爱的音乐';
                break;
            case 5:
                $data['title'] = '我爱的影视';
                break;
        }

        //获取该大类下的所有标签
        $data['list'] = $db->getLabelInfoByArr(NULL, $type);
        if(is_bool($data['list'])){
            $data['list'] = array();
            return $data;
        }

        //对用户添加、未添加的标签进行区分
        foreach($data['list'] as $key => $value){
            if(in_array($value['id'], $user)){
                $data['list'][$key]['isSet'] = true;
            }else{
                $data['list'][$key]['isSet'] = false;
            }
        }

        return $data;
    }

/**
 * 更新用户标签
 *
 * @return  
 */
    public static function updateUserLabel(){
        $labelArr = Library_Share::getRequest('labelArr', Library_Share::ARRAY_DATA);
        $type = Library_Share::getRequest('type', Library_Share::INT_DATA);
        if(!is_array($labelArr) || is_bool($type)){
            return 1;//数据不足
        }
        // var_dump('labelArr:', implode(',', $labelArr));
        // echo '<br/>';

        $user_ybid = $_SESSION['yb_user_info']['yb_userid'];

        $db = new Data_User();
        //获取用户已经添加过的标签
        $userLabelId = $db->getUserLabelIdList($user_ybid);
        // var_dump('userLabelID:', implode(',', $userLabelId));
        // echo '<br/>';

        //获取该type下的所有标签id
        $typeList = $db->getLabelInfoByArr(NULL, $type);
        $typeArr = array();
        foreach($typeList as $key => $value){
            $typeArr[] = $value['id'];
        }
        // var_dump('typeArr:', implode(',', $typeArr));
        // echo '<br/>';

        //获取用户在该type下的所有已添加的id
        $userLabelId = array_intersect($userLabelId, $typeArr);
        // var_dump('userLabelID:', implode(',', $userLabelId));
        // echo '<br/>';

        //获得需要添加的标签id
        $add = array_diff($labelArr, $userLabelId);
        // var_dump('add:', implode(',', $add));
        // echo '<br/>';

        //添加用户标签
        if(count($add) != 0){
            $result = $db->addLabelLog($user_ybid, $add);
            if(!$result){
                return 2;//添加标签失败
            }
        }

        //获得需要删除的标签id
        $delete = array_diff($userLabelId, $labelArr);
        // var_dump('delete:', implode(',', $delete));
        // echo '<br/>';

        //删除用户标签
        if(count($delete) != 0){
            $result = $db->deleteLabelLog($user_ybid, $delete);
            if(!$result){
                return 3;//杀出标签失败
            }
        }

        return true;
    }

/**
 * 获取两用户的相似度
 *
 * @param  int $user1 
 * @param  int $user2 
 * @return array        
 */
    public static function similarRate($user1, $user2){

        if(!$user1){
            return 1;//没有数据
        }

        if($user1 == $user2){
            return 2;//不能和自己比较相似度哦
        }

        $db = new Data_User();
        $ret = $db->similarRate($user1, $_SESSION['yb_user_info']['yb_userid']);
        if(is_bool($ret)){
            return 3;//比较失败
        }

        return array($ret);
    }

/**
 * 获取随机好友推荐
 *
 * @return  
 */
    public static function circle(){
        $db_User = new Data_User();
        $circle = $db_User->circle($_SESSION['yb_user_info']['yb_userid']);
        if(!$circle){
            return array();
        }
        foreach($circle as $key => $value){
            $circle[$key]['rate'] = $db_User->similarRate($value['ybid'], $_SESSION['yb_user_info']['yb_userid']);
        }

        return $circle;
    }

}
?>