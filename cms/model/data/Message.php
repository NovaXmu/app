<?php
/**
*
*    @copyright  Copyright (c) 2016 Xuxinang
*    All rights reserved
*
*    file:            Message.php
*    description:     留言列表
*
*    @author Xuxinang
*    @license Apache v2 License
*
**/

/**
*  留言列表读取
*/
class Data_Message
{

    function __construct() {}

    /**
     * 获取表中指定信息
     * @param  int $processed 处理标识
     *             0:未处理/1:已处理
     * @param  int $messagetype 留言类型 
     *             0:cooperation/1:bugfrontend/2:bugbackend/3:bugother/4:suggestion
     * @param  int $page 页数 
     * @return array 全部信息
     * @author Xuxinang
     */
    public function getMessageData($processed = 0, $messagetype = 0, $page = 0) {
        switch ($messagetype) {
            case 1:
            case 2:
            case 3:
                $field = 'messageid,username,contactway,mailbox,messagecontent,attention,';
                break;
            
            case 0:
            case 4:
                $field = 'messageid,username,contactway,mailbox,messagecontent,';
                break;
        }

        switch ($processed) {
            case 0:
                $field .= 'updatetime';
                break;
            
            case 1:
                $field .= 'creationtime,updatetime';
                break;
        }

        $db = Vera_Database::getInstance();
        $where = array('messagetype' => $messagetype, 'processed' => $processed);
        $appends = 'ORDER BY updatetime DESC LIMIT '.($page * 10).',10';
        $res = $db->select('message_Message', $field, $where, NULL, $appends);
        return $res;
    }
}

?>
