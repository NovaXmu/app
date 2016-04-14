<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/19
 * Time: 20:22
 * File: Participant.php
 * Description: 跟抢票有关的操作，包括获取当前可抢票列表，获取某抢票活动详情，进行抢票，以及退票，获取本人抢票信息如token等。用户身份绑定在之前的厦大绑定那边，用那个统一接口
 */

class Action_Api_User_Participant
{
    public $userId;//Vera_User表的主键id，每个用户有个唯一值
    public $userXmuNum; //用户厦大学号

    function __construct()
    {
        $this->userId = $_SESSION['user_id'];
        $this->userXmuNum = $_SESSION['user_xmuNum'];
    }

    function run ()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : null;
        switch($m) {
            case 'getTicketList' :
                $this->getTicketList();
                break;
            case 'getTicketContent' :
                $this->getTicketContent();
                break;
            case 'ticket':
                $this->doTicketTask();//抢票
                break;
            case 'returnTicket':
                $this->returnTicket();
                break;
            case 'getHistoryRecord':
                $this->getUserHistoryRecord();
                break;
            case 'getUserInfo':
                $this->getUserInfo();
                break;
            default:
                echo json_encode(array('errno' => 1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }
    }

    function getTicketList()
    {
        $data = new Data_Db(array('ID' => $this->userId));
        $ticketList = $data->getParticipantTicketList();
        $now = date('Y-m-d H:i:s');
        if (!empty($ticketList)) {
            foreach ($ticketList as $index => $row) {
                if ($row['startTime'] > $now) {
                    break;//ticketList已按开始时间排序，若开始时间大于当前时间，则余票统统为100%,无需后续操作
                }
                $left = $data->isLeft($row);
                $ticketList[$index]['leftTickets'] = $left;
                $result = $data->getUserResultInAct($row['actID'], $this->userId);
                $userUsedTimes = $data->countOfUser($row['actID']);
                $ticketList[$index]['result'] = $result['resultStr'];
                $ticketList[$index]['accessToken'] = isset($result['accessToken']) ? $result['accessToken'] : null;
                $ticketList[$index]['userUsedTimes'] = $userUsedTimes;
            }
        }

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $ticketList), JSON_UNESCAPED_UNICODE);
    }

    function getTicketContent()
    {
        if (!isset($_GET['actID']) || !is_numeric($_GET['actID'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '活动id非法'), JSON_UNESCAPED_UNICODE);
            return;
        }
        $service = new Service_Participant();
        $detail = $service->getActDetail($_GET['actID']);
        if (is_string($detail)) {
            echo json_encode(array('errno' => 1, 'errmsg' => $detail), JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $detail['content']), JSON_UNESCAPED_UNICODE);
    }

    //进行抢票
    function doTicketTask()
    {
        if(!isset($_POST['actID']) || !is_numeric($_POST['actID'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'));
            return;
        }

        $service = new Service_Participant();
        $msg = $service->doTicketTask($_POST['actID'], $this->userId);
        if (is_numeric($msg) && strlen($msg) == 12) {
            //12位数字即抢票成功，返回的是凭证号
            echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $msg), JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(array('errno' => 1, 'errmsg' => $msg), JSON_UNESCAPED_UNICODE);
    }

    function returnTicket()
    {
        if (!isset($_POST['log_id']) || !is_numeric($_POST['log_id'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => 'id非法'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = new Data_Db(array('ID' => $this->userId));

        $log = $data->getLog($_POST['log_id'])[0];
        if (empty($log)) {
            echo json_encode(array('errno' => 1, 'errmsg' => 'id非法'), JSON_UNESCAPED_UNICODE);
            return;
        } else if (($log['userID'] != $this->userId) || $log['result'] == 0 || $log['isUsed'] == 1) {
            //用户不一致&未抢中&已使用三种情况下不可退票
            echo json_encode(array('errno' => 1, 'errmsg' => '非法请求'), JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$data->unSignTicket($log)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '退票失败，请稍后重试'), JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
    }

    function getUserHistoryRecord()
    {
        $data = new Data_Db(array('ID' => $this->userId));
        $info = $data->getUserHistory($this->userId);
        $res = array();
        foreach($info as  $row) {
            if (isset($res[$row['actID']])) {
                //同活动只取最新的一条记录
                $res[$row['actID']]['leftTimes'] -= 1;
                $res[$row['actID']]['leftTimes'] = $res[$row['actID']]['leftTimes'] < 0 ? 0 : $res[$row['actID']]['leftTimes'];//很多历史数据不正确导致了这个剩余次数为负的情况，手动置0.。。
                continue;
            }
            $row['leftTimes'] = $row['times'] - 1;
            $res[$row['actID']] = $row;
        }
        $res = array_values($res);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $res), JSON_UNESCAPED_UNICODE);
    }

    function getUserInfo()
    {
        $data = new Data_Db(array('ID' => $_SESSION['openid']));
        $user = $data->getUser($_SESSION['openid']);
        if (empty($user[0]['realName'])) {
            Vera_Autoload::changeApp('wechat');
            $jwc = new Data_Xmu_Jwc(null);
            $handle = $jwc->getLoginHandle($user[0]['xmuId'], $user[0]['xmuPassword']);
            if ($handle) {
                try{
                    $info = $jwc->getInfo($handle);
                    Vera_Autoload::reverseApp();
                    $user[0]['realName'] = isset($info['xm']) ? $info['xm'] : null;
                    $user[0]['college'] = isset($info['yxsh_displayvalue']) ? $info['yxsh_displayvalue'] : null;
                    $user[0]['telephone'] = isset($info['sjh']) ? $info['sjh'] : null;
                    $user[0]['sex'] = isset($info['xbdm_displayvalue']) ? $info['xbdm_displayvalue'] : null;
                    $user[0]['grade'] = isset($info['xznj']) ? $info['xznj'] : null;
                    $data->modifyUser($_SESSION['openid'], $user[0]);
                    unset($_SESSION['user_id']);//信息更新后session信息也需要更新，即下次请求Auth重新更新信息
                } catch (Exception $e) {
                    $temp = $e->getMessage();
                }
            }
        }

        unset($user[0]['xmuPassword']);
        $user[0]['isAdmin'] = isset($_SESSION['admin_id']) ? 1 : 0;
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $user[0]), JSON_UNESCAPED_UNICODE);
    }


}