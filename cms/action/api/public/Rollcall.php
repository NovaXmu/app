<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Rollcall.php
*	description:	开放会场签到Api
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
*  无权限验证抢票 Api
*/

class Action_Api_Public_Rollcall
{
    function __construct() {}

    public static function run()
    {
        if (!isset($_GET['m'])) {
            return false;
        }
        switch ($_GET['m']) {
            case 'submit':
                return self::_submit();
            case 'list':
                return self::_managerList();

            case 'download':
                return self::_download(); 

            case 'logout':
                return self::_logout();
            default:
                break;
        }
        $ret = array('errno'=>'020101','errmsg'=>'参数错误');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
    }

    private static function _submit()
    {
        if (!isset($_GET['owner']) || !is_numeric($_GET['owner']) || !isset($_GET['name']) || !isset($_GET['startTime']) || !isset($_GET['endTime']) || !isset($_GET['extra']) || !isset($_GET['award']) || !isset($_GET['limitConds'])) {
            $ret = array('errno'=>'1','errmsg'=>'参数不对');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $data = new Data_Rollcall();
        if(isset($_GET['md5']) && !empty($_GET['md5']))
        {
            if(self::_auth())
            {
                $ret = $data->setAct($_GET['owner'],$_GET['name'],$_GET['startTime'],$_GET['endTime'], $_GET['extra'], $_GET['award'], $_GET['limitConds'], $_GET['md5']);
                echo json_encode($ret, JSON_UNESCAPED_UNICODE);
                return $ret;
            }

            else
            {
                $ret = array('errno'=>'1','errmsg'=>'未通过身份验证');
                echo json_encode($ret, JSON_UNESCAPED_UNICODE);
                return false;//未通过身份验证不能修改活动信息
            }
        }
        $ret = $data->setAct($_GET['owner'],$_GET['name'],$_GET['startTime'],$_GET['endTime'], $_GET['extra'], $_GET['award'], $_GET['limitConds']);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return $ret;

    }
    
    private static function _managerList()
    {
        $ret = array('errno'=>'0','errmsg'=>'OK','data'=>array());

        //身份验证
        if(!self::_auth())
        {
            $ret['errno'] = '1';
            $ret['errmsg'] = '身份验证未通过';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        
        $owner = $_SESSION["num"];
        $db = Vera_Database::getInstance();
        $lastDay = date("Y-m-d H:i:s", time() - 86400 * 3);//显示已过期三天的抢票活动
        $condition = "endTime >= '{$lastDay}' and owner = {$owner}"; 
        //$condition = "owner = {$owner}";
        $result = $db->select('rollcall_Board','*',$condition,NULL,'order by startTime desc');
        if ($result) {
            foreach ($result as $key => $value)
            {
                $result[$key]['limitConds'] = json_decode($value['limitConds'], true);
            }
            $ret['data'] = $result;
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return $ret;
    }


    private  static function _download()
    {
        $ret = array('errno'=>'0','errmsg'=>'ok');
        if (!isset($_GET['act'])) {
            $ret = array('errno'=>'1','errmsg'=>'参数不完整');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $act = $_GET['act'];
        if(!self::_auth())
        {
            $ret = array('errno'=>'1','errmsg'=>'未通过身份验证');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        $data = new Data_Rollcall();

        $actInfo = $data->getActInfo($act);
        if ($actInfo['owner'] != $_SESSION["num"]) {
            $ret = array('errno'=>'1','errmsg'=>'此活动不属于您');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if($actInfo['isPassed'] != 1)
        {
        	$ret = array('errno'=>'1','errmsg'=>'活动还未通过审核');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        $list = $data->getCheckinList($act);
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


    private static function _logout()
    {
        session_destroy();
        //header("Location: /");
        return true; 
    }

    private static function _auth()
    {
        //身份验证,返回值true即通过验证，false未通过
        if(isset($_SESSION["num"]) && isset($_SESSION["pwd"]))
        {
            return true;
        }   
        /*if(isset($_SESSION['level']) && $_SESSION['level'] > 0)
        {
        	return true;
        }*/
        if(!isset($_GET['owner']) || !isset($_GET['pwd']))
        {
            return false;
        }

        Vera_Autoload::changeApp('wechat');
        $handle = Data_Xmu_Jwc::getLoginHandle($_GET['owner'],$_GET['pwd']);
        Vera_Autoload:: reverseApp();
        if(!$handle)
        {
           //$ret['errno'] = 1;
           //$ret['errmsg'] = '学号或密码错误';
           return false;
        }
        $_SESSION["num"] = $_GET['owner'];
        $_SESSION["pwd"] = $_GET['pwd'];

        return true;

    }

    /*private static function _getList($actID)
    {
        $db = Vera_Database::getInstance();
        $sql = "select vera_User.xmu_num as num, ticket_Log.accessToken as access_token, ticket_Log.isUsed as isUsed, ticket_Log.time as time from vera_User, ticket_Log where vera_User.id = ticket_Log.userID and ticket_Log.actID = {$actID} and ticket_Log.result = 1";
        $result = $db->query($sql);
        if ($result) {
            $act = $db->select('ticket_List','name',array('actID'=>$actID));
            header('Content-Type: text/xls');
            header('Content-type:application/vnd.ms-excel;charset=utf-8');
            header('Content-Disposition: attachment;filename=厦大易班抢票平台_'.$act[0]['name'].'.xls');
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');

            $table_data  = '<table border="1">
                                <tr>
                                    <th colspan="4">'.$act[0]['name'].'</th>
                                </tr>
                                <tr>
                                    <th>学号</th>
                                    <th>凭证号</th>
                                    <th>时间</th>
                                    <th>是否已使用</th>
                                </tr>';
            foreach ($result as $line) {
                $table_data .= '<tr>
                                    <td align="center" style="vnd.ms-excel.numberformat:@">' . $line['num'] . '</td>
                                    <td align="center" style="vnd.ms-excel.numberformat:@">' . $line['access_token'] . '</td>
                                    <td align="center" style="vnd.ms-excel.numberformat:yyyy-mm-dd HH:mm:dd">' . $line['time'] . '</td>';
                $temp = $line['isUsed'] == 1 ? '是' : '否';
                $table_data .=      '<td align="center">' . $temp . '</td>';
                                '</tr>';
            }
            $table_data .='</table>';
            echo $table_data;
        }
    }*/
}
?>