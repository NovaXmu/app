<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Ticket.php
*    description:     抢票平台信息操作类
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  抢票平台信息操作
*/
class Data_Ticket
{

    function __construct() {}

    /**
     * 待审核活动列表
     * @return  array  活动数组
     */
    public function getNeedReviewActs()
    {
        $db = Vera_Database::getInstance();
        $cond = array('isPassed'=>0);
        $append = 'order by startTime asc';
        $result = $db->select('ticket_List', '*', $cond, NULL, $append);
        if ($result) {
            foreach ($result as &$each) {
                $each['resultCount'] = $this->_getResultCount($each['actID'],1);
                $each['count'] = $this->_getCount($each['actID']);
            }
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * 待开始活动列表
     * @return  array  活动数组
     */
    public function getReadyActs()
    {
        $db = Vera_Database::getInstance();
        $cond = 'isPassed = 1 and startTime > \''.date("Y-m-d H:i:s").'\'';
        $append = 'order by startTime asc';
        $result = $db->select('ticket_List', '*', $cond, NULL, $append);
        if ($result) {
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * 正在进行的活动列表
     * @return  array  活动数组
     */
    public function getOnGoingActs()
    {
        $db = Vera_Database::getInstance();
        $cond = 'isPassed = 1 and startTime <= now() and now() <= endTime';
        $result = $db->select('ticket_List', '*', $cond);
        if ($result) {
            foreach ($result as &$each) {
                $each['resultCount'] = $this->_getResultCount($each['actID'],1);
                $each['count'] = $this->_getCount($each['actID']);
            }
            return $result;
        }
        else {
            return false;
        }
    }

    /**
    * 正在进行的活动页码
    * @return  int   正在进行活动的页码数
    */
    public function getOnGoingPages()
    {
        $db = Vera_Database::getInstance();
        $cond = 'isPassed = 1 and startTime <= now() and now() <= endTime';
        $result = $db->select('ticket_List', 'count(*)', $cond);
        if ($result) {
            var_dump($result);
            return $result['count(*)'];
        }
        else {
            return 0;
        }
    }

    /**
     * 获取已终止的活动
     * @param   int $isPassed  正常结束的活动或是审核未通过的活动
     * @return  array             活动数组
     */
    public function getEndActs($isPassed = 1)
    {
        $db = Vera_Database::getInstance();

        $append = NULL;
        if ($isPassed == 1) {
            $cond = 'isPassed = 1 and now() >= endTime';//已正常结束的活动
            $append = 'order by endTime desc limit 0,5';//显示最新的五个
        }
        else {
            $cond = array('isPassed' => -1);//审核未通过的活动
            $append = 'order by actID desc limit 0,5';
        }

        $result = $db->select('ticket_List', '*', $cond, NULL, $append);
        if ($result) {
            foreach ($result as &$each) {
                $each['resultCount'] = $this->_getResultCount($each['actID'],1);
                $each['count'] = $this->_getCount($each['actID']);
            }
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * 设置审批结果
     * @param  int  $actID     活动 id
     * @param integer $isPassed  审批结果，-1未通过，0为审核，1审核通过
     */
    public function setPass($actID, $isPassed = 1)
    {
        $db = Vera_Database::getInstance();

        $row = array('isPassed'=>$isPassed);
        $cond = array('actID'=>$actID);
        $db->update('ticket_List', $row, $cond);

        return true;
    }

    /**
     * 统计参与人数
     * @param   int  $actID   活动 id
     * @param  integer $result  中奖情况
     * @return  int           人数
     */
    private function _getCount($actID)
    {
        $db = Vera_Database::getInstance();
        $cond = array('actID'=>$actID);
        $ret = $db->selectCount('ticket_Log', $cond, 'distinct');
        return $ret;
    }

    /**
     * 统计中奖/未中奖的人数
     * @param   int  $actID   活动 id
     * @param  integer $result  中奖情况
     * @return  int           人数
     */
    private function _getResultCount($actID, $result = 1)
    {
        $db = Vera_Database::getInstance();
        $cond = array('actID'=>$actID, 'result'=>$result);
        $ret = $db->selectCount('ticket_Log', $cond);
        return $ret;
    }

}

?>
