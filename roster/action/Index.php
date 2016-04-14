<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Index.php
*   description:      Action for Index.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Index{

    function __construct(){}

    public function run(){
        if(isset($_SESSION['manager'])&&!empty($_SESSION['manager']))
            header('Location:/roster/admin?m=index');
        else
            header('Location:/roster/admin?m=login');
        exit();
    }
}
?>