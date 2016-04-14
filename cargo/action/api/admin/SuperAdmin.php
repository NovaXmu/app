<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			SuperAdmin.php
*	description:	SuperAdmin.php,超级管理员处理与管理员相关操作，变更权限，更改超级管理员账号等
*
*	@author Nili
*	@license Apache v2 License
*
**/

class Action_Api_Admin_SuperAdmin
{
    function run ()
    {
        if (!isset($_GET['m']) || empty($_GET['m'])) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数有误'), JSON_UNESCAPED_UNICODE);
            return null;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != -1) {//-1为超级管理员
            echo json_encode(array('errno' => 1, 'errmsg' => '非法请求'), JSON_UNESCAPED_UNICODE);
            return null;
        }
        switch ($_GET['m']) {
            case 'changePrivilege':
                $msg = $this->changePrivilege();
                break;
            case 'getAdminPrivilege':
                $msg = $this->getAdminPrivilege();
                break;
            case 'updateSuperAdmin':
                $msg = $this->updateSuperAdmin();
                break;
            case 'download':
                $msg = $this->download();
                break;
            case 'changeUser':
                $msg = $this->changeUser();
                break;
            case 'getUserList':
                $msg = $this->getUserList();
                break;
            default:
                $msg = '非法m';
                break;
        }
        if ($msg) {
            echo json_encode(array('errno' => 1, 'errmsg' => $msg), JSON_UNESCAPED_UNICODE);
        }
    }

    function changePrivilege()
    {
        if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id']) || !isset($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
            return '参数有误';
        }

        $data = new Data_Db();
        if (empty($data->getCategory(array('id' => $_POST['category_id'])))) {
            return '类别不存在';
        }
        $data->setPrivilege($_POST['user_id'], $_POST['category_id']);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function updateSuperAdmin()
    {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            return '参数错误';
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        if (strlen($username) > 10) {
           return '用户名必须在10个字符以内';
        }

        Vera_Cache::getInstance()->set('cargo_root', md5($username . $password), 0);

        $_SESSION['user_id'] = -1;
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function download()
    {
        $start = isset($_GET['start']) ? date('Y-m-d H:i:s', strtotime($_GET['start'])) : null;
        $end = isset($_GET['end']) ? date('Y-m-d H:i:s', strtotime($_GET['end'])) : null;

        $type = isset($_GET['type']) ? 1 : 0;//默认0，领取类型物品
        $data = new Data_Db();
        $res = $data->downloadTakeLog($start, $end, $type);
        header('Content-Type: text/xls');
        header('Content-type:application/vnd.ms-excel;charset=utf-8');
        header("Content-Disposition: attachment;filename=\""."物资借用.xls"."\"");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        if ($type == 0) {
            echo "处理时间\t物品名称\t申请数量\t申请人\t实际数量\t管理员\t备注\n";
            if (!empty($res)) {
                foreach ($res as $row) {
                    echo 	$row['dealt_time'] ."\t" .
                        $row['item_name'] ."\t" .
                        $row['apply_amount'] ."\t" .
                        $row['user_name'] ."\t" .
                        $row['real_amount'] ."\t";
                    $admin = $data->getUser(array('id' => $row['admin_id']));
                    echo $admin[0]['name'] . "\t " . $row['remark'] . "\n";
                }
            }
        } else {
            echo "处理时间\t物品名称\t申请数量\t申请人\t实际数量\t管理员\t归还时间\t备注\n";
            if (!empty($res)) {
                foreach ($res as $row) {
                    echo 	$row['dealt_time'] ."\t" .
                        $row['item_name'] ."\t" .
                        $row['apply_amount'] ."\t" .
                        $row['user_name'] ."\t" .
                        $row['real_amount'] ."\t";
                    if ($row['back_time']) {
                        echo date('Y-m-d', strtotime($row['back_time'])) . "\t";
                    } else {
                        echo "\t";
                    }

                    $admin = $data->getUser(array('id' => $row['admin_id']));
                    echo $admin[0]['name'] . "\t " . $row['remark'] . "\n";
                }
            }
        }
        return '';
    }

    function changeUser()
    {
        $user = array();
        if (isset($_POST['name']) && !empty($_POST['name'])) {
            $user['name'] = $_POST['name'];
        }

        if (isset($_POST['mobile_phone']) && !empty($_POST['mobile_phone'])) {
            if ($this->checkPhoneNum($_POST['mobile_phone'])) {
                $user['mobile_phone'] = $_POST['mobile_phone'];
            } else {
                return '手机号非法';
            }
        }

        if (isset($_POST['email']) && !empty($_POST['email'])) {
            if ($this->checkEmail($_POST['email'])) {
                $user['email'] = $_POST['email'];
            } else {
                return '邮箱非法';
            }
        }
        if (isset($_POST['deleted'])) {
            $user['deleted'] = 1;
        }
        if(empty($user)) {
            return '参数非法';
        }

        $data = new Data_Db();
        if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
            $user = $data->getUser(array('id' => $_POST['user_id'], 'deleted' => 0));
            if (empty($user)) {
                return '被修改用户不存在或已被删除';
            }
            $data->setUser($user, $_POST['user_id']);
        } else {
            if (!isset($user['name']) || !isset($user['mobile_phone']) || !isset($user['email']) || isset($user['deleted'])) {
                return '参数非法';
            } else if (!empty($data->getUser(array('mobile_phone' => $_POST['mobile_phone'])))) {
                return '该手机号已被录入';
            }
            $data->setUser($user);
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function checkPhoneNum($phoneNum)
    {
        return true;
    }

    function checkEmail($email)
    {
        return true;
    }

    function getAdminPrivilege()
    {
        if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
            return '参数错误';
        }

        $data = new Data_Db();

        $privilegeLog = $data->getPrivilege(array('user_id' => $_GET['user_id'], 'deleted' => 0));
        $ret = array();
        foreach ($privilegeLog as $row) {
            $category = $data->getCategory(array('id' => $row['category_id']));
            $row['category_name'] = $category[0]['name'];
            $ret[] = $row;
        }

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $ret), JSON_UNESCAPED_UNICODE);
        return '';
    }

    function getUserList()
    {
        $data = new Data_Db();
        $allAdmins = $data->getPrivilege(array('deleted' => 0));

        $allAdminIds = array_column($allAdmins, 'user_id');
        $allAdminIds = array_flip(array_flip($allAdminIds));//去重复

        $allUsers = $data->getUser(array('deleted' => 0));

        if (!empty($allAdminIds)) {
            foreach ($allUsers as $index => $user) {
                $allUsers[$index]['isAdmin'] = 0;
                if (in_array($user['id'], $allAdminIds)) {
                    $allUsers[$index]['isAdmin'] = 1;
                }
            }
        }

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $allUsers), JSON_UNESCAPED_UNICODE);
        return '';
    }

}
