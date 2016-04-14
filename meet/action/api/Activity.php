<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Activity.php
*   description:      Action for Activity.php
*
*   @author Linjun
*   @license Apache v2 License
*
**/

class Action_Api_Activity extends Action_Base{
    function __construct(){}

    public function run(){
        $m = Library_Share::getRequest('m');
        if(is_bool($m) && !$m){
            $return = array('errno' => '1', 'errmsg' => '参数不对');
        }else{
            switch($m){
            case 'joinActivity'://test pass
                $return = $this->joinActivity();
                break;
            case 'quitActivity'://test pass
                $return = $this->quitActivity();
                break;
            case 'addActivity'://test pass
                $return = $this->addActivity();
                break;
            case 'updateActivity':
                $return = $this->updateActivity();
                break;
            case 'deleteActivity'://test pass
                $return = $this->deleteActivity();
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
 * 参与活动
 *
 * @return boolean 是否成功参与活动
 */
    private function joinActivity(){

        $activity_id = Library_Share::getRequest('activity_id', Library_Share::INT_DATA);

        if(is_bool($activity_id) && !$activity_id){
            $ret = array('errno' => '1','errmsg' => '参数错误');
            return $ret;
        }

        $result = Service_Activity::joinActivity($activity_id);

        if(!is_bool($result)){
            switch($result){
                case 1:
                    $ret = array('errno'=> '1', 'errmsg' => '该活动id对应的活动不存在');
                    break;
                case 2:
                    $ret = array('errno'=> '1', 'errmsg' => '您尚未加入该部落，不可参与该部落的活动哦~');
                    break;
                case 3:
                    $ret = array('errno'=> '1', 'errmsg' => '啊！ 已经过了报名截止时间了诶 T^T');
                    break;
                case 4:
                    $ret = array('errno'=> '1', 'errmsg' => '啊！ 这个活动太抢手了，已经没有名额了哦 T^T');
                    break;
                case 5:
                    $ret = array('errno'=> '1', 'errmsg' => '您已经加入了该活动，请前往我加入的活动查看~');
                    break;
                case 6:
                    $ret = array('errno'=> '1', 'errmsg' => '加入活动失败了诶 T T');
                    break;
                case 7:
                    $ret = array('errno'=> '1', 'errmsg' => '部落积分增加失败了 T^T');
                    break;
                case 8:
                    $ret = array('errno'=> '1', 'errmsg' => '您在部落内的积分增加失败了 T^T');
                    break;
                case 9:
                    $ret = array('errno'=> '1', 'errmsg' => '你的interper值增加失败了 T^T');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0','errmsg' => 'ok');
        return $ret;
    }

/**
 * 退出活动
 *
 * @return boolean 是否成功退出活动
 */
    private function quitActivity(){
        $ret = array('errno' => '0','errmsg' => 'ok');

        $activity_id = Library_Share::getRequest('activity_id', Library_Share::INT_DATA);

        if(is_bool($activity_id) && !$activity_id){
            $ret = array('errno' => '1','errmsg' => '参数错误');
            return $ret;
        }

        $result = Service_Activity::quitActivity($activity_id);

        if(!is_bool($result)){
            switch($result){
                case 1:
                    $ret = array('errno'=> '1', 'errmsg' => '该活动id对应的活动不存在');
                    break;
                case 2:
                    $ret = array('errno' => '1', 'errmsg' => '你是该活动的主办者，不能退出活动哦~');
                    break;
                case 3:
                    $ret = array('errno' => '1', 'errmsg' => '已经过了报名截止时间,不能退出活动了诶');
                    break;
                case 4:
                    $ret = array('errno'=> '1', 'errmsg' => '您没有加入该活动');
                    break;
                case 5:
                    $ret = array('errno'=> '1', 'errmsg' => '退出该活动失败');
                    break;
                case 6:
                    $ret = array('errno'=> '1', 'errmsg' => '部落积分减少失败了 真是太幸运了！T^T');
                    break;
                case 7:
                    $ret = array('errno'=> '1', 'errmsg' => '您在部落内的积分减少失败了 真是太幸运了！');
                    break;
                case 8:
                    $ret = array('errno'=> '1', 'errmsg' => '你的interper值减少失败了 真是太幸运了！');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0','errmsg' => 'ok');
        return $ret;
    }

/**
 * 发起活动
 * 
 *@return   boolean 是否成功发起活动
 */
    private function addActivity(){

        $data = Library_Share::getRequest('data', Library_Share::ARRAY_DATA);

        if(is_bool($data)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Activity::addActivity($data);

        if(!is_bool($result)){
            switch($result){
                case 1:
                    $ret = array('errno'=> '1', 'errmsg' => '参数错误');
                    break;
                case 2:
                    $ret = array('errno' => '1', 'errmsg' => '截止时间不能在当前时间之前哦~');
                    break;
                case 3:
                    $ret = array('errno' => '1', 'errmsg' => '活动内容长度不能超过50字哦');
                    break;
                case 4:
                    $ret = array('errno'=> '1', 'errmsg' => '没有加入该部落，不可以发起活动哦');
                    break;
                case 5:
                    $ret = array('errno'=> '1', 'errmsg' => '发布活动失败了， 请稍后再试');
                    break;
                case 6:
                    $ret = array('errno'=> '1', 'errmsg' => '部落积分增加失败了！T T');
                    break;
                case 7:
                    $ret = array('errno'=> '1', 'errmsg' => '您在部落内的积分增加失败了！ T T');
                    break;
                case 8:
                    $ret = array('errno'=> '1', 'errmsg' => '你的interpersonal值增加失败了！ T T');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0','errmsg' => 'ok');
        return $ret;
    }

/**
 * 修改活动
 *
 * @return  
 */
    private function updateActivity(){
        $data = Library_Share::getRequest('data', Library_Share::ARRAY_DATA);

        if(is_bool($data)){
            $ret = array('errno' => '1', 'errmsg' => '没有数据');
            return $ret;
        }

        $result = Service_Activity::updateActivity($data);

        if(!is_bool($result)){
            switch($result){
                case 1:
                    $ret = array('errno'=> '1', 'errmsg' => '参数错误');
                    break;
                case 2:
                    $ret = array('errno'=> '1', 'errmsg' => '不存在该活动');
                    break;
                case 3:
                    $ret = array('errno' => '1', 'errmsg' => '不是活动发起人，不可以修改活动哦');
                    break;
                case 4:
                    $ret = array('errno'=> '1', 'errmsg' => '已经过了报名截止时间,不能修改活动了哦');
                    break;
                case 5:
                    $ret = array('errno'=> '1', 'errmsg' => '截止时间不能在当前时间之前');
                    break;
                case 6:
                    $ret = array('errno'=> '1', 'errmsg' => '活动内容长度不能超过50字哦');
                    break;
                case 7:
                    $ret = array('errno'=> '1', 'errmsg' => '修改活动失败了！ T T');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0','errmsg' => 'ok');
        return $ret;
    }

/**
 * 删除活动
 *
 * @return boolean 是否成功删除活动
 */
    private function deleteActivity(){
        
        $activity_id = Library_Share::getRequest('activity_id', Library_Share::INT_DATA);

        if(is_bool($activity_id)){
            $ret = array('errno' => '1', 'errmsg' => '没有选择活动');
            return $ret;
        }

        $result = Service_Activity::deleteActivity($activity_id);

        if(!is_bool($result)){
            switch($result){
                case 1:
                    $ret = array('errno'=> '1', 'errmsg' => '不存在该活动');
                    break;
                case 2:
                    $ret = array('errno' => '1', 'errmsg' => '您不是该部落的成员哦');
                    break;
                case 3:
                    $ret = array('errno' => '1', 'errmsg' => '您不是该活动的发起人哦');
                    break;
                case 4:
                    $ret = array('errno'=> '1', 'errmsg' => '该活动已经过了报名截止时间，不能删除了哦');
                    break;
                case 5:
                    $ret = array('errno'=> '1', 'errmsg' => '获取成员参与列表失败了，所以暂时不能取消活动，请稍后再试哦');
                    break;
                case 6:
                    $ret = array('errno'=> '1', 'errmsg' => '取消活动失败了， 请稍后再试');
                    break;
                case 7:
                    $ret = array('errno'=> '1', 'errmsg' => '部落积分减少失败了！T T');
                    break;
                case 8:
                    $ret = array('errno'=> '1', 'errmsg' => '您在部落内的积分减少失败了！ T T');
                    break;
                case 9:
                    $ret = array('errno'=> '1', 'errmsg' => '你的interper值减少失败了！ T T');
                    break;
            }
            return $ret;
        }

        $ret = array('errno' => '0','errmsg' => 'ok');
        return $ret;
    }

}
?>