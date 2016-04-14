<?php

class Action_Api_User_Attention
{
    public function run() {
        $messageid = isset($_GET['messageid']) ? $_GET['messageid'] : null;

        $message = array('errno' => 300, 'errmsg' => 'Failed');
        if (! is_numeric($messageid)) {
        	$message['errno'] = 301;
            $message['errmsg'] = "Parameter Error - messageid=$messageid";       
        } else {
        	$db = new Data_Db();
        	$message['errno'] = 0;
        	$message['errmsg'] = $db->updateAttention($messageid);
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }
}

?>