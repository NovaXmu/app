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
class Service_Message{

    public function getMessageList($isLimited, $isPassed = 1, $page = 1){
        $db = new Data_Message();
        if($isLimited){
            $appends = 'order by time desc limit ' . ($page-1)*20 .', 20';
            $conds = array('isPassed' => 1);
        }else{
            $appends = 'order by time desc';
            $conds = array('isPassed' => $isPassed);
        }
        return $db->getMessageList($conds, $appends);
    }

    /**
     * 添加留言
     * @param String $content 留言内容
     */
    public function addMessage($rows){
        $rows['time'] = date('Y-m-d H:i:s');
        if($rows['content'] == '祝厦大95岁生日快乐!')
            $rows['isPassed'] = 1;
        else
            $rows['isPassed'] = -1;
        $user = new Data_User();
        $user->addIp();
        $db = new Data_Message();
        return $db->addMessage($rows);
    }

    public function setAudit(){
        
    }
}
?>