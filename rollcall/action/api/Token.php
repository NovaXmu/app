<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Token.php
*    description:     会场签到二维码token
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
* 二维码token处理类
*/
class Action_Api_Token extends Action_Base
{
    function __construct() {}

    public function run()
    {
        switch ($_GET['m']) {
            case 'refresh':
                $this->_refresh();
                break;
            default:
                break;
        }
        return true;
    }

    /**
     * 刷新二维码的token
     */
    private function _refresh()
    {
        set_time_limit(30);//防止出现异常导致PHP超时从而引发前端无响应。
        $resource = $this->getResource();
        $act = $_GET['act'];
        $num = $resource['num'];
        if (!Service_Info::isActBelong($act, $num)) {
            $ret = array('errno'=>'2','errmsg'=>'此活动不属于您或未通过审核');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $token = Service_Func::newTokenFor($act);

        $ret = array('errno'=>'0',
            'data'=> array(
                'token' => $token
                )
            );
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}
?>
