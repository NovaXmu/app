<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Push.php
*    description:     推送平台Api
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
* 推送面板 api
*/
class Action_Api_Push extends Action_Base
{

    function __construct() {}

    public function run()
    {
        if (!isset($_GET['m'])) {
            return false;
        }

        switch ($_GET['m']) {
            case 'needReview':
                return $this->_getNeedReviewTasks();
                break;
            case 'ready':
                return $this->_getReadyTasks();
                break;
            case 'finished':
                return $this->_getFinishedTasks();
                break;
            case 'disabled':
                return $this->_getDisabledTasks();
                break;


            case 'add':
                if (!isset($_POST['content']) || !isset($_POST['time'])) {
                    return false;
                }
                return $this->_new($_POST['time'], $_POST['content']);
                break;
            case 'update':
                if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['newTime'])) {
                    return false;
                }
                return $this->_update($_GET['id'], $_GET['newTime']);
                break;
            case 'review':
                if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['review']) || !is_numeric($_GET['review'])) {
                    return false;
                }
                return $this->_setReview($_GET['id'],$_GET['review']);
                break;
            case 'reset':
                if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                    return false;
                }
                return $this->_resetTask($_GET['id']);
                break;

            default:
                return false;
                break;
        }
    }

    private function _getNeedReviewTasks()
    {
        $service = new Service_Wechat_Push();
        $tasks = $service->getNeedReviewTasks();

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $tasks;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getReadyTasks()
    {
        $service = new Service_Wechat_Push();
        $tasks = $service->getReadyTasks();

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $tasks;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getFinishedTasks()
    {
        $service = new Service_Wechat_Push();
        $tasks = $service->getFinishedTasks();

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $tasks;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getDisabledTasks()
    {
        $service = new Service_Wechat_Push();
        $tasks = $service->getDisabledTasks();

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';
        $ret['data'] = $tasks;

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _new($time, $content)
    {
        $ret = array('errno'=>'0','errmsg'=>'OK');

        $service = new Service_Wechat_Push();
        $content = json_decode($content,true);
        $service->newTask($time, $content);

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _update($id, $time)
    {
        $service = new Service_Wechat_Push();
        $service->updateTime($id, $time);

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    /**
     * 重置任务状态，恢复未推送未审核
     * @param  integer  $id    任务 id
     */
    private function _resetTask($id)
    {
        $service = new Service_Wechat_Push();
        $service->resetTask($id);

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    /**
     * 设置任务审核状态
     * @param  int  $id        任务 id
     * @param integer $review  审核结果
     */
    private function _setReview($id, $review = 0)
    {
        $service = new Service_Wechat_Push();
        $service->setReview($id, $review);

        $ret['errno'] = 0;
        $ret['errmsg'] = 'OK';

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}

?>
