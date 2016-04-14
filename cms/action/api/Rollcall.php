<?php
/**
*
*    @copyright  Copyright (c) 2015 Nili
*    All rights reserved
*
*    file:            Rollcall.php
*    description:    会场签到Api
*
*    @author Nili
*    @license Apache v2 License
*    
**/

/**
*  会场签到Api
*/
class Action_Api_Rollcall extends Action_Base
{

    function __construct() {}

    public function run()
    {
        if (!isset($_GET['m'])) {
            $ret = array('errno' => '1', 'errmsg' => 'm缺失');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
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
                if (!isset($_GET['md5']) || !isset($_GET['isPassed']) || !is_numeric($_GET['isPassed'])) {
                    return false;
                }
                return $this->_setPass($_GET['md5'],$_GET['isPassed']);
                break;

            default:
                return false;
                break;
        }
        $ret = array('errno'=>'1','errmsg'=>'参数错误');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
    }

    private function _getNeedReviewActs()
    {
        $temp = array();
        $data = new Data_Rollcall();
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
        $data = new Data_Rollcall();
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
        $data = new Data_Rollcall();
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
        $data = new Data_Rollcall();
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

    private function _setPass($md5, $isPassed = 1)
    {
        $data = new Data_Rollcall();
        $data->setPass($md5, $isPassed);

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
?>
