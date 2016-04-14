<?php
class Action_Index{

    function __construct(){}

    public function run(){
        header("location:/anniversary/message");
        exit;
    }
}
?>