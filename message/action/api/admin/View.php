<?php

class Action_Api_Admin_View
{
    public function run() {
        $processed_id = isset($_GET['processed']) ? $_GET['processed'] : null;
        $messagetype_id = isset($_GET['messagetype']) ? $_GET['messagetype'] : null;        
        $page = isset($_GET['page']) ? $_GET['page'] : null;
        
        $message = array('errno' => 300, 'errmsg' => 'Failed');
        if (! is_numeric($processed_id) || ! is_numeric($messagetype_id) || ! is_numeric($page)) {
            $message['errno'] = 301;
            $message['errmsg'] = "Parameter Error - processed=$processed_id&messagetype=$messagetype_id&page=$page";
        } else {
            $db = new Data_Db();
            $message['errno'] = 0;
            $message['errmsg'] = $db->getMessageData($processed_id, $messagetype_id, $page);            
        }  
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }
}

?>