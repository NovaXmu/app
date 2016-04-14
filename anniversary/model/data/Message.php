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
class Data_Message{
    function __construct(){}

    public function addMessage($rows){
        $db = Vera_Database::getInstance();
        return $db->insert('anniversary_Message', $rows);
    }

    public function setMessage($conds, $rows){
        $db = Vera_Database::getInstance();
        return $db->update('anniversary_Message', $rows, $conds);
    }

    public function getMessageList($conds, $appends){
        $db = Vera_Database::getInstance();
        return $db->select('anniversary_Message', '*', $conds, NULL, $appends);
    }

    public function getMessageCount(){
        $db = Vera_Database::getInstance();
        return $db->selectCount('anniversary_Message');
    }
}
?>