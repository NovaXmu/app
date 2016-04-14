<?php

class Action_Api_User_Push
{
    public function run() {
        $db = new Data_Db();
        $message = array('errno' => 0, 'errmsg' => $db->getBugMessage());
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }
}

?>