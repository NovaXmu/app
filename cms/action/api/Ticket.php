<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Ticket.php
*    description:     抢票平台Api
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  抢票平台Api
*/
class Action_Api_Ticket extends Action_Base
{

    function __construct() {}

    public function run()
    {
        if (!isset($_GET['m'])) {
            return false;
        }

        switch ($_GET['m']) {
            case 'needReview':
                return $this->_getNeedReviewActs();
                break;
            case 'ready':
                return $this->_getReadyActs();
                break;
            case 'onGoing':
                return $this->_getOnGoingActs();
                break;
            case 'end':
                return $this->_getEndActs(1);
                break;
            case 'notPassed':
                return $this->_getEndActs(-1);
                break;

            case 'review':
                if (!isset($_POST['actID']) || !is_numeric($_POST['actID']) || !isset($_POST['isPassed']) || !is_numeric($_POST['isPassed'])) {
                    return false;
                }
                return $this->_setPass($_POST['actID'],$_POST['isPassed']);
                break;

            default:
                return false;
                break;
        }
    }

    private function _getNeedReviewActs()
    {
        $temp = array();
        $data = new Data_Ticket();
        $temp = $data->getNeedReviewActs();

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getReadyActs()
    {
        $temp = array();
        $data = new Data_Ticket();
        $temp = $data->getReadyActs();//即将开始的活动列表

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getOnGoingActs()
    {
        $temp = array();
        $data = new Data_Ticket();

        $temp = $data->getOnGoingPages();//正在进行的活动页码
        

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);


        $temp = $data->getOnGoingActs();//正在进行的活动列表

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getEndActs($isPassed = 1)
    {
        $temp = array();
        $data = new Data_Ticket();
        if ($isPassed == 1) {
            $temp = $data->getEndActs(1);//已结束的活动列表
        }
        else {
            $temp = $data->getEndActs(-1);//审核未通过的活动列表
        }

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _setPass($actID, $isPassed = 1)
    {
        $data = new Data_Ticket();
        $data->setPass($actID, $isPassed);

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
?>
