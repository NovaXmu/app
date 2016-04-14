<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Index.php
*    description:    CMS 控制面板主页
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
* CMS 控制面板
*/
class  Action_Board_Index extends Action_Base
{

    function __construct()
    {

    }

    public function run()
    {
        $view = new Vera_View(true);//设置为true开启debug模式

        $conf = Vera_Conf::getAppConf('authority');
        $models = $conf['Board']['Panel'];

        $level = $_SESSION['level'];
        $list = array();
        $userModels = Data_Admin::getAdminMenu($_SESSION['id']);
        $userModels = array_column($userModels, 'privilege');
        foreach ($models as $key => $value) {
            if (in_array($key, $userModels) || $level == 10) {//根据权限判断是否显示该模块
                $temp['key'] = strtolower($key);
                $temp['name'] = $value['name'];
                $list[] = $temp;
            }
        }
        $view->assign('user',$_SESSION['name']);
        $view->assign('models',$list);
        $view->display('cms/Board.tpl');
        return true;
    }
}

?>
