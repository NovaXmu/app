<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/23
 * Time: 23:14
 * File: Admin.php
 * Description:管理员相关功能实现：审核活动，获取活动列表(未通过，正在进行，已结束）
 */

class Action_Api_Admin_Admin
{
    public $adminId;//cms_Admin表中对应的id，此处能被Auth放行的管理员必须具有Ticket权限

    function __construct()
    {
        $this->adminId = $_SESSION['admin_id'];

    }

    function run()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : null;
        switch ($m) {
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
                echo json_encode(array('errno' => 1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
                break;
        }
        return false;
    }

    private function _getNeedReviewActs()
    {
        $data = new Data_Db();
        $temp = $data->getNeedReviewActs();

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getReadyActs()
    {
        $data = new Data_Db();
        $temp = $data->getReadyActs();//即将开始的活动列表

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getOnGoingActs()
    {
        $data = new Data_Db();
        $temp = $data->getOnGoingActs();//正在进行的活动列表

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $temp;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getEndActs($isPassed = 1)
    {
        $data = new Data_Db();
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
        $data = new Data_Db();
        $data->setPass($actID, $isPassed);

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
