<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Login.php
*    description:     登录校验 api
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  登录校验
*/
class Action_Api_Login extends Action_Base
{
    //@temp: 网络文化节活动列表
    static $actName = array(
        '"厦门大学"官微',
        '青春厦大',
        '厦大就业指导中心',
        'i厦大',
        '新传说',
        '厦大经院学生会',
        '厦大管院学生宣传中心',
        '景润小学',
        '厦大石语',
        'FL工作室',
        '厦大易班',
        'E维工作室'
    );

    //@temp: 网络文化节活动管理员学工号列表，与上表一一对应
    static $adminList = array(
        '10120132202261',
        '15220142201665',
        '13820122200379',
        '30920122202571',
        '30620132203469',
        '15220132202145',
        '17420142200689',
        '19020142202832',
        '25120142201525',
        '11920142203039',
        '17420132200702',
        '17620131151309'
    );

    function __construct() {}

    public function run()
    {
        // @temp: 网络文化节临时权限校验
        if (isset($_POST['user']) && in_array($_POST['user'], self::$adminList)) {
            return $this->_tempCulture();
        }
        if (isset($_GET['m']) && $_GET['m'] == 'logout') {
            return $this->_logout();
        }
        else {
            return $this->_login();
        }
    }

    private function _login()
    {

        if (!isset($_POST['user']) || !isset($_POST['pwd'])) {
            $ret = array('errno'=>'020301','errmsg'=>'参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $ret = array('errno'=>'0','errmsg'=>'OK', 'to'=>'/cms/board/index');
        $username = htmlspecialchars($_POST['user']);
        $password = htmlspecialchars($_POST['pwd']);

        $data = new Data_Admin();
        $info = $data->getInfo($username);

        if (!$info) {
            $ret = array('errno'=>'020302','errmsg'=>'用户名或密码错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        $token = Library_Auth::generateToken($username, $password);

        if ($info['token'] != $token) {//密码校验失败
            unset($_SESSION);
            $ret = array('errno'=>'020303','errmsg'=>'用户名或密码错误');
        }
        else {
            $_SESSION['id'] = $info['id'];//管理员 id
            $_SESSION['name'] = $info['nickname'];//昵称
            $_SESSION['level'] = $info['level'];//管理等级

            Vera_Log::addNotice('name',$_SESSION['name']);
            Vera_Log::addNotice('id',$_SESSION['id']);
            Vera_Log::addNotice('level',$_SESSION['level']);
        }

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _logout()
    {
        session_destroy();
        header("Location: /");
        return true;
    }

    // @temp: 网络文化节临时登录校验
    private function _tempCulture()
    {
        if (!isset($_POST['user']) || !isset($_POST['pwd'])) {
            $ret = array('errno'=>'020301','errmsg'=>'参数错误');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $user = $_POST['user'];
        $pwd = $_POST['pwd'];

        Vera_Autoload::changeApp('wechat');
        $result = Data_Xmu_Jwc::getLoginHandle($user, $pwd);
        Vera_Autoload::reverseApp();
        if (!$result) {
            //登录失败
            unset($_SESSION);
            $ret = array('errno'=>'020303','errmsg'=>'用户名或密码错误');
        } else {
            $_SESSION['culture'] = array_search($user, self::$adminList);//通过学号找到对应的展位的序号作为id
            $ret = array('errno'=>'0','errmsg'=>'OK', 'to'=>'/rollcall/pay');//跳转到扫码页面
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

}

?>
