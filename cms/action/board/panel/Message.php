<?php
/**
*
*    @copyright  Copyright (c) 2016 Xuxinang 
*    All rights reserved
*
*    file:            Message.php
*    description:     留言面板
*
*    @author Xuxinang
*    @license Apache v2 License
*
**/

/**
*  留言面板
*/
class Action_Board_Panel_Message
{
    public function run()
    {
        $view = new Vera_View(true);
        $db = new Data_Message();
        $data = $db->getMessageData();            
        
        $view->assign('data', $data);
        $view->display('cms/panel/MessageInfo.tpl');
        return true;
    }
}


?>
