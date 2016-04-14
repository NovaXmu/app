<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Message.php
*   description:      Action for Message.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Api_Message{
    public function run(){
        //登录 不需要检查是否有登录
        if(!isset($_GET['m']) || empty($_GET['m'])){
            return false;
        }

        switch($_GET['m']){
            case 'getMoreMessage'://获取更多留言 用于弹幕
                $this->getMoreMessage();
                break;
            case 'addMessage'://添加留言 test pass
                $this->addMessage();
                break;
            case 'getMessageList'://获取留言，用户审核 test pass
                $this->getMessageList();
                break;
            case 'auditMessage'://审核留言 test pass
                $this->auditMessage();
                break;
            case 'addAuditer'://添加审核人员 test pass
                $this->addAuditer();
                break;
            case 'setAuditer'://设置审核人员 
                $this->setAuditer();
                break;
        }
        return true;
    }

    private function getMoreMessage(){
        $page = Library_Share::getRequest('page');
        if(is_bool($page) || empty($page)){
            Vera_Log::addVisitLog('res', '参数有误');   
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $service = new Service_Message();
        $list = $service->getMessageList(true, 1, $page);
        $db = new Data_Message();
        $count = $db->getMessageCount();
        $data = array('list' => $list,
            'count' => $count
            );
        echo json_encode(array('errno' => 0, 'errmsg' => $data), JSON_UNESCAPED_UNICODE);
        return;
    }


    /**
     * 添加留言，必须已经授权微信
     */
    private function addMessage(){
        $content = Library_Share::getRequest('content');
        if(is_bool($content) || empty($content)){
            Vera_Log::addVisitLog('res', '参数有误');   
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $service = new Service_Message();
        $name = Library_Share::getRequest('name');
        $identity = Library_Share::getRequest('identity');
        $enrollmentYear = Library_Share::getRequest('enrollmentYear');
        $rows = array(
            'content' => $content,
            'name' => is_bool($name) ? '匿名' : $name,
            'identity' => is_bool($identity) ? NULL : $identity,
            'enrollmentYear' => is_bool($enrollmentYear) ? NULL : $enrollmentYear
            );
        $ret = $service->addMessage($rows);
        if(is_bool($ret)){
            Vera_Log::addVisitLog('res', '添加留言失败');   
            echo json_encode(array('errno' => 1, 'errmsg' => '添加留言失败'), JSON_UNESCAPED_UNICODE);
            return;
        }
        Vera_Log::addVisitLog('res', 'ok');
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return;
    }

    /**
     * 获取审核的留言
     * @return array 
     */
    private function getMessageList(){
        $isPassed = Library_Share::getRequest('isPassed');
        if(is_bool($isPassed) || ($isPassed != 0 && $isPassed != -1 && $isPassed != 1)){
            Vera_Log::addVisitLog('res', '参数有误');   
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $service = new Service_Message();
        $list = $service->getMessageList(false, $isPassed);
        Vera_Log::addVisitLog('res', 'ok');
        echo json_encode(array('errno' => 0, 'errmsg' => $list), JSON_UNESCAPED_UNICODE);
        return;
    }

    /**
     * 设置留言的状态
     * @return array 
     */
    private function auditMessage(){
        $messageId = Library_Share::getRequest('messageId');
        $isPassed = Library_Share::getRequest('isPassed');
        if(is_bool($messageId) || empty($messageId) || is_bool($isPassed) || ($isPassed != -1 && $isPassed != 1)){
            Vera_Log::addVisitLog('res', '参数有误');   
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $db = new Data_Message();
        $ret = $db->setMessage(array('id' => $messageId), array('isPassed' => $isPassed));
        if(!$ret){
            Vera_Log::addVisitLog('res', '留言审核设置失败');   
            echo json_encode(array('errno' => 1, 'errmsg' => '留言审核设置失败'), JSON_UNESCAPED_UNICODE);
            return;
        }
        Vera_Log::addVisitLog('res', 'ok');
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return;
    }

    private function addAuditer(){
        if(!isset($_GET['xmu']) || empty($_GET['xmu'])) {
            Vera_Log::addVisitLog('res', '参数有误');   
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $db = new Data_User();
        if(!$db->addAuditer($_GET['xmu'])){
            echo json_encode(array('errno' => 1, 'errmsg' => '添加审核人员失败'), JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(array('errno' => 0, 'errmsg' => '添加审核人员成功'), JSON_UNESCAPED_UNICODE);
        return;
    }

    private function setAuditer(){
        if(!isset($_GET['auditerId']) || empty($_GET['auditerId']) || !isset($_GET['isDelete']) || empty($_GET['isDelete'])) {
            Vera_Log::addVisitLog('res', '参数有误');   
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $db = new Data_User();
        if(!$db->updateAuditer($_GET['auditerId'], $_GET['isDelete'])){
            echo json_encode(array('errno' => 1, 'errmsg' => '更新审核人员失败'.Vera_Database::getLastSql()), JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(array('errno' => 0, 'errmsg' => '更新审核人员成功'), JSON_UNESCAPED_UNICODE);
        return;
    }

    //  /**
    //  * 审核人员绑定
    //  * @return array 
    //  */
    // private function linkin(){
    //     $telephone = Library_Share::getRequest('telephone');
    //     if(is_bool($telephone) || empty($telephone)) {
    //         Vera_Log::addVisitLog('res', '参数有误');   
    //         echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
    //         return;
    //     }

    //     $db = new Data_User();
    //     var_dump($_SESSION['user']);
    //     $user = $db->linkinAuditer($_SESSION['user']['id'], $telephone);
    //     if(empty($user)){
    //         Vera_Log::addVisitLog('res', '您不是审核人员，无法绑定');   
    //         echo json_encode(array('errno' => 1, 'errmsg' => '您不是审核人员，无法绑定'), JSON_UNESCAPED_UNICODE);
    //         return;
    //     }
    //     $_SESSION['user'] = $user;
    //     Vera_Log::addVisitLog('res', 'ok');
    //     echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
    //     return;
    // }
}
?>