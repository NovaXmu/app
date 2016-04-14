<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Act.php
*    description:    活动增删改api入口
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
*
*/
class Action_Api_Act extends Action_Base
{
    function __construct() {}

    public function run()
    {
        switch ($_GET['m']) {
            case 'add':
                return $this->_add();
                break;
            case 'update':
                return $this->_update();
                break;
            case 'download':
                return $this->_download();
                break;

            default:
                break;
        }
        return false;
    }

    private static function _add()
    {
        if (!isset($_POST[''])) {
            # code...
        }
    }

    private static function _update()
    {
        $resource = $this->getResource();
        $num = $resource['num'];
        $act = $_GET['act'];
        if (!Service_Info::isActBelong($act, $num)) {
            $ret = array('errno'=>'2','errmsg'=>'此活动不属于您');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        # code...
    }

    private function _download()
    {
        if (!isset($_GET['act'])) {
            return false;
        }
        $act = $_GET['act'];
        $resource = $this->getResource();
        if (!Service_Info::isActBelong($act, $resource['num'])) {
            $ret = array('errno'=>'2','errmsg'=>'此活动不属于您');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $actInfo = Data_Db::getActInfo($act);
        $list = Data_Db::getCheckinList($act);
        header('Content-Type: text/xls');
        header('Content-type:application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename=厦大易班扫码签到_'.$actInfo['name'].'.xls');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $excel = new Vera_View(true);
        $excel->assign('name', $actInfo['name']);
        $excel->assign('list', $list);
        $excel->display('rollcall/Excel.tpl');
        return true;
    }
}
 ?>
