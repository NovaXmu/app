<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/13
 * Time: 15:17
 * File: FIxWxFail.php
 * Description: 学校账号网薪用尽，导致一些用户网薪发放失败，重发&推送&提供查询功能
 */

class Action_Api_Public_FixWxFail
{
    function run()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : 'reAward';
        switch($m) {
            case "reAward" :
                $this->reAward();
                break;
            case "query" :
                $this->query();
                break;
            default:
                echo json_encode(array('errno' => 0, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }
    }

    function reAward()
    {
        set_time_limit(0);
        $data = new Data_Db();
        $reAwardData = $data->getReAwardData();
        foreach($reAwardData as $row) {
            if (!empty($row['res'])) {
                continue;
            }

            Vera_Autoload::changeApp('yiban');
            $res = Data_Yiban::awardSalary($row['yb_uid'], $row['access_token'], $row['money'], "补发,{$row['msg']},{$row['from']}");
            Vera_Autoload::reverseApp();
            if ($res) {
//                $data->updateWxLog(array('award' => $row['money']), array('yb_uid' => $row['yb_uid'], 'randNum' => $row['msg'], 'time' => $row['old_time']));
                $data->updateReWardLog(array('res' => 'success', 'time' => date('Y-m-d H:i:s')), array('id' => $row['id']));
            }
        }

    }

    function query()
    {

    }

}