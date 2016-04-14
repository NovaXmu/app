<?php
/**
 * Created by PhpStorm.
 * User: ni
 * Mail: nl_1994@foxmail.com
 * Date: 2016/3/26
 * Time: 15:33
 * File: Xmu.php
 * Description:跟学校有关的操作，目前只有获取各学院名称
 */

class Action_Api_Public_Xmu
{
    function run ()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : null;
        switch($m) {
            case 'getColleges':
                $this->getAllColleges();
                break;
            default:
                echo json_encode(array('errno' => 1, 'errmsg' => '非法m'), JSON_UNESCAPED_UNICODE);
        }
    }

    function getAllColleges()
    {
        $db = Vera_Database::getInstance();
        $colleges = $db->select('sport_Xueyuan', 'xueyuan');
        $colleges = array_column($colleges, 'xueyuan');
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $colleges), JSON_UNESCAPED_UNICODE);
    }
}