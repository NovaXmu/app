<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Aa.php
*    description:     动态回复信息获取
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  动态回复信息获取
*/
class  Data_Reply_Aa extends Data_User
{

    function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function getNum()
    {
        return $this->getStuNum();
    }

    public function isPay(){
        $num = $this->getStuNum();
        $db = Vera_Database::getInstance();
        return $db->select('checkin_Log', '*', array('isPay' => -1, 'xmu_num' => $num));
    }

}
?>
