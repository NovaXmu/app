<?php

class Action_Api_User_LeaveMessage
{
    public function run() {
    	$mailbox = isset($_POST['mailbox']) ? $_POST['mailbox'] : null;
    	$messagetype_id = isset($_POST['messagetype']) ? $_POST['messagetype'] : null;

        $message = array('errno' => 300, 'errmsg' => 'Failed');
        if (! filter_var($mailbox, FILTER_VALIDATE_EMAIL) || ! is_numeric($messagetype_id)) {
        	$message['errno'] = 301;
            $message['errmsg'] = "Parameter Error - mailbox=$mailbox&messagetype=$messagetype_id";
        } else {
        	$db = new Data_Db();
        	$message['errno'] = 0;
        	$message['errmsg'] = $db->insertMessage($_POST['username'], $_POST['contactway'], $mailbox, $messagetype_id, $_POST['messagecontent']);
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }
}

?>