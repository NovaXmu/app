<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            User.php
*    description:     用户列表
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  用户列表读取
*/
class Data_User
{

    function __construct() {}

    public function getUserList()
    {
        $db = Vera_Database::getInstance();
        $result = $db->select('User', 'id,xmuId xmu_num,linkXmuTime xmu_linkTime,yibanUid yiban_uid, linkYibanTime yiban_linkTime',array('isLinkedXmu'=>'1'));
        if (!$result) {
            return false;
        }
        $count = count($result);

        $ret['count'] = $count;
        $ret['users'] = $result;

        $xmuLinkCount = $db->select('User', 'count(*)', array('isLinkedXmu' => 1))[0]['count(*)'];
        $yibanLinkCount = $db->select('User', 'count(*)', array('isLinkedYiban' => 1))[0]['count(*)'];
        $totalLinkCount = $db->query("SELECT count(*) FROM User WHERE isLinkedXmu=1 OR isLinkedYiban=1")[0]['count(*)'];
        $ret['xmuLinkCount'] = $xmuLinkCount;
        $ret['yibanLinkCount'] = $yibanLinkCount;
        $ret['totalLinkCount'] = $totalLinkCount;
        return $ret;
    }

    public function getUserInfo()
    {

    }
}

?>
