<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/24
 * Time: 22:01
 * File: Participant.php
 * Description:用户绑定
 */

class Action_Api_Public_Participant
{
    function run()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : 'linkin';
        switch($m) {
            case 'linkin':
                $this->linkIn();
                break;
            case 'modifyUserInfo':
                $this->modifyUserInfo();
                break;
            default:
                echo json_encode(array('errno' =>1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }
    }

    function linkIn()
    {
        if (!isset($_POST['num']) || !isset($_POST['password'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return;
        }

        $resource = array('openid' => $_SESSION['openid']);
        Vera_Autoload::changeApp('wap');
        $data = new Data_Db($resource);
        $ret = array('errno' => 0, 'errmsg' => 'ok');
        if (!$data->xmuCheck($_POST['num'], $_POST['password'])) {
            $ret = array('errno' => 1, 'errmsg' => '用户名或密码错误');
        }else if (!$data->updateXmu($_POST['num'], $_POST['password'])) {
            $ret = array('errno' => 1, 'errmsg' => '该微信号已绑定其他学号或该学号已被其他微信号绑定');
        }
        Vera_Autoload::reverseApp();
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    function modifyUserInfo()
    {
        $keys = array('realName', 'college', 'sex', 'grade', 'identity');
        if (empty($_SESSION['openid'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '请微信客户端进入'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $data = new Data_Db();
        $user = $data->getUser($_SESSION['openid']);
        if (empty($user)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '请先绑定厦大账号'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $user_id = $user[0]['id'];
        $params = array();
        foreach($keys as $key) {
            if (isset($_POST[$key]) && !empty($_POST[$key]) && empty($user[0][$key])) {
                $params[$key] = $_POST[$key];
            }
        }

        if (isset($_POST['telephone']) && preg_match('|1[35478]{1}\d{9}|', $_POST['telephone'])) {
            $params['telephone'] = $_POST['telephone'];
        }
        if (empty($params)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return false;
        }

        if (!$data->modifyUser($_SESSION['openid'], $params)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '请确认信息有改动且格式合法'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $openid = $_SESSION['openid'];
        unset($_SESSION);//信息更新后session信息也需要更新，即下次请求Auth重新更新信息
        $_SESSION['user_id'] = $user_id;
        $_SESSION['openid'] = $openid;
        $user = $data->getUser($_SESSION['openid']);
        $_SESSION['user_name'] = $user[0]['realName'];
        $_SESSION['user_xmuNum'] = $user[0]['xmuId'];
        echo json_encode(array('errno' =>0 , 'errmsg' => 'ok'));
        return true;

    }
}