<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Auth.php
*    description:    权威性验证action
*
*    @author Linjun
*    @license Apache v2 License
*
**/

/**
* 权威性验证
*/
class Action_Auth extends Action_Base
{
	public static function run()
	{

//        session_start();
        if (isset($_SESSION['yb_user_info']) && !empty($_SESSION['yb_user_info']) && $_SESSION['yb_user_info']['token_expires'] >= date("Y-m-d H:i:s"))
        {
            parent::setResource($_SESSION['yb_user_info']);
            //var_dump($_SESSION['yb_user_info']);
            $ret = Data_User::addUser($_SESSION['yb_user_info']['yb_userid'], $_SESSION['yb_user_info']['yb_username'], $_SESSION['yb_user_info']['yb_sex']);
            
            if(!Library_Share::isApi()){
                $log = Library_Share::getLog();
                $log = json_encode($log, JSON_UNESCAPED_UNICODE);
                Vera_Log::addLog('visit', $log);
            }
            if(!$ret){
                return false;
            }
            return true;
        }

        header("Location: /yiban/EntryFromYiban?appName=meet/person?m=index");
        exit();
	}

}
?>
