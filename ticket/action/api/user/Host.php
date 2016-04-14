<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/19
 * Time: 21:18
 * File: Host.php
 * Description: 获取当前用户已申请的活动列表，下载名单&在线看名单，兑换，修改活动信息。活动申请在public目录下。
 */

class Action_Api_User_Host
{
    public $userTel; //用户手机号

    function __construct()
    {
        $this->userTel = $_SESSION['user_tel'];
    }

    function run()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : null;
        switch($m) {
            case 'getList':
                $this->getList();
                break;
            case 'download':
                $this->download();
                break;
            case 'modify':
                $this->modify();
                break;
            case 'exchange':
                $this->exchange();
                break;
            default:
                echo json_encode(array('errno' => 1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }
    }

    function getList()
    {

    }

    function download()
    {

    }

    function modify()
    {

    }

    function exchange()
    {

    }
}