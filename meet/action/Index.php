<?php
class Action_Index extends Action_Base{

    function __construct(){}

    public function run(){
        header("location:/meet/person?m=index");
    }
}
?>