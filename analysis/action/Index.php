

<?php
/**
 *
 *	@copyright  Copyright (c) 2015 Nili
 *	All rights reserved
 *
 *	file:			Index.php
 *	description:    日志分析首页，展示数据待定
 *
 *	@author Nili
 *	@license Apache v2 License
 *
 **/

class Action_Index
{
    public function run()
    {
        echo 'scxsew';
        $service = new Service_Access();
        $service->basicInfoByHour($_GET['start']);
    }
}

?>