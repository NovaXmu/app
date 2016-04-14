<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Ticket.php
*    description:     开放抢票 API
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  无权限验证抢票 Api
*/
class Action_Api_Public_Ticket
{
    function __construct() {}

    public static function run()
    {
        if (!isset($_GET['m'])) {
            return false;
        }
        switch ($_GET['m']) {
            case 'submit':
                if (!isset($_POST['name']) || !isset($_POST['telephone']) || !isset($_POST['content']) || !isset($_POST['chance']) || !isset($_POST['total']) || !isset($_POST['times']) || !isset($_POST['startTime']) || !isset($_POST['endTime'])) {
                    break;
                }
                return self::_submit($_POST);
                break;

            case 'list':
                if (!isset($_GET['telephone']) || !is_numeric($_GET['telephone'])){
                    break;
                }
                $_SESSION['host_telephone'] = $_GET['telephone'];
                return self::_managerList($_GET['telephone']);
                break;

            case 'getList':
                if (!isset($_GET['id']) || !is_numeric($_GET['id'])){
                    break;
                }
                return self::_getList($_GET['id']);
                break;

            default:
                break;
        }
        $ret = array('errno'=>'020101','errmsg'=>'参数错误');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
    }

    private static function _submit($act){
        if(isset($act['actID']) && !empty($act['actID'])){
            $ret = self::_update($act);
        }else{
            $ret = self::_insert($act);
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;

    }

    private static function _insert($act)
    {
        $db = Vera_Database::getInstance();
        $insert = array(
            'name'      =>$act['name'],
            'owner'     =>$act['telephone'],
            'content'   =>$act['content'],
            'total'     =>$act['total'],
            'times'     =>$act['times'],
            'startTime' =>$act['startTime'],
            'endTime'   =>$act['endTime'],
            'chance'    =>$act['chance'],
            'type'      =>'random'
            );
        $row = $db->insert('ticket_List',$insert);
        $ret = array('errno'=>'0','errmsg'=>'OK');
        if (!$row) {
            $ret = array('errno'=>'020102','errmsg'=>'提交出错');
        }
        Vera_Autoload::changeApp('wechat');
        Data_Push_Func::pushToVeraAdmin('有新的抢票申请，请尽快审核');
        Vera_Autoload::reverseApp();
        return $ret;
    }

    /**
    * 活动修改功能
    *@author linjun
    *@rewrite by nili,isPassed字段名修正，注释update操作后的错误判断。
    *原因：1，update出错可能性小。2，遇到提交数据与之前数据相同，即未发生更改时，影响行数为0，会返回提交出错的错误提示，不科学。
    */
    private static function _update($act){
        $db = Vera_Database::getInstance();
        $update = array(
            'name'      =>$act['name'],
            'owner'     =>$act['telephone'],
            'content'   =>$act['content'],
            'total'     =>$act['total'],
            'times'     =>$act['times'],
            'startTime' =>$act['startTime'],
            'endTime'   =>$act['endTime'],
            'chance'    =>$act['chance'],
            'type'      =>'random',
            "isPassed"    =>0
            );
        $cond = array('actID' => $act['actID']);

        $info = $db->select('ticket_List', '*', array('actOD' => $act['actID']));
        if (empty($info) || $info[0]['startTime'] < date('Y-m-d H:i:s')) {
            $ret = array('errno'=>'020102','errmsg'=>'活动非法或已开始，不能修改');
            return $ret;
        }
        $row = $db->update('ticket_List', $update, $cond);
        $ret = array('errno'=>'0','errmsg'=>'OK');
        // if (!$row) {
        //     $ret = array('errno'=>'020102','errmsg'=>'提交出错');
        // }
        //清缓存
        $cache = Vera_Cache::getInstance();
        $cache->delete('ticket_'.$act['actID'].'_left');
        $cache->delete('ticket_' . $act['actID']);
        return $ret;
    }

    private static function _managerList($telephone)
    {
        $ret = array('errno'=>'0','errmsg'=>'OK','data'=>array());
        $db = Vera_Database::getInstance();
        $lastDay = date("Y-m-d H:i:s", time() - 86400 * 3);//显示已过期三天的抢票活动
        $condition = "endTime >= '{$lastDay}' and owner = {$telephone}";
        $result = $db->select('ticket_List','actID,name,content,isPassed,total,times,startTime,endTime,chance',$condition,NULL,'order by startTime desc');
        if ($result) {
            for ($i=0; $i < count($result); $i++) {
                if ($result[$i]['startTime'] <= date("Y-m-d H:i:s", time())) {//若活动已开始，统计抢票信息
                    $result[$i]['count'] = $db->selectCount('ticket_Log',array('actID'=>$result[$i]['actID']));
                    $result[$i]['resultCount'] = $db->selectCount('ticket_Log',array('actID'=>$result[$i]['actID'],'result'=>'1'));
                    $result[$i]['usedCount'] = $db->selectCount('ticket_Log',array('actID'=>$result[$i]['actID'],'isUsed'=>'1'));
                }
                else {
                    $result[$i]['count'] = 0;
                    $result[$i]['resultCount'] = 0;
                    $result[$i]['usedCount'] = 0;
                }
            }
            $ret['data'] = $result;
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private static function _getList($actID)
    {
        $db = Vera_Database::getInstance();
//        $sql = "select User.xmu_num as num, ticket_Log.accessToken as access_token, ticket_Log.isUsed as isUsed, ticket_Log.time as time from vera_User, ticket_Log where vera_User.id = ticket_Log.userID and ticket_Log.actID = {$actID} and ticket_Log.result = 1";
        $sql = "select User.xmuId as num, User.realname as name, ticket_Log.accessToken as access_token, ticket_Log.isUsed as isUsed, ticket_Log.time as time from User, ticket_Log where User.id = ticket_Log.userID and ticket_Log.actID = {$actID} and ticket_Log.result = 1";
        $result = $db->query($sql);
        if ($result) {
            $act = $db->select('ticket_List','name',array('actID'=>$actID));
            $filename = "厦大易班抢票平台_".$act[0]['name'].".xls";
            header('Content-Type: text/xls');
            header('Content-type:application/vnd.ms-excel;charset=utf-8');
            header("Content-Disposition: attachment;filename=\"".$filename."\"");
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');

            $table_data  = '<table border="1">
                                <tr>
                                    <th colspan="4">'.$act[0]['name'].'</th>
                                </tr>
                                <tr>
                                    <th>学号</th>
                                    <th>姓名</th>
                                    <th>凭证号</th>
                                    <th>时间</th>
                                    <th>是否已使用</th>
                                </tr>';
            foreach ($result as $line) {
                $table_data .= '<tr>
                                    <td align="center" style="vnd.ms-excel.numberformat:@">' . $line['num'] . '</td>
                                    <td align="center" style="vnd.ms-excel.numberformat:@">' . $line['name'] . '</td>
                                    <td align="center" style="vnd.ms-excel.numberformat:@">' . $line['access_token'] . '</td>
                                    <td align="center" style="vnd.ms-excel.numberformat:yyyy-mm-dd HH:mm:dd">' . $line['time'] . '</td>';
                $temp = $line['isUsed'] == 1 ? '是' : '否';
                $table_data .=      '<td align="center">' . $temp . '</td>';
                                '</tr>';
            }
            $table_data .='</table>';
            echo $table_data;
        }
    }
}
?>
