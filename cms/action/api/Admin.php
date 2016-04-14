<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Admin.php
*    description:     平台管理面板Api
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  平台管理面板Api
*/
class Action_Api_Admin extends Action_Base
{

    function __construct() {}

    public function run()
    {
        if (!isset($_GET['m'])) {
            return false;
        }
        switch ($_GET['m']) {
            case 'reset':
                if (!isset($_POST['user'])) {
                    break;
                }
                return $this->_resetPwd($_POST['user']);

            case 'updateLevel':
                if (!isset($_GET['user']) || !isset($_GET['level']) || !is_numeric($_GET['level'])) {
                    break;
                }
                return $this->_updateLevel($_GET['user'], $_GET['level']);

            case 'update':
                if (!isset($_SESSION['id']) || $_SESSION['id'] < 0 || !isset($_POST['name']) || !isset($_POST['password'])) {
                    break;
                }
                return $this->_update($_SESSION['id'], $_POST['name'], $_POST['password']);

            case 'delete':
                if (!isset($_POST['user'])) {
                    break;
                }
                return $this->_deleteUser($_POST['user']);
            case 'create':
                return $this->_createAdmin();

            case 'setPass':
                return $this->_setPass();

            case 'getAllPrivileges':
                return $this->_getAllPrivileges();

            case 'addPrivilege':
                return $this->_addPrivilege();

            case 'removePrivilege':
                return $this->_removePrivilege();

            case 'getAdminPrivilege':
                return $this->_getPrivilegeForAdmin();
            default:
                break;
        }
        $ret = array('errno' => '020201','errmsg' => '参数错误' );
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return false;
    }

    /**
     * 新增管理员，新增后默认isPassed为0,用户名密码及邮箱为必填，用户名不可重复。
     * @return bool
     */
    private function _createAdmin()
    {
        $fields = array('id', 'user', 'nickname', 'level', 'openid', 'email');
        $params = $_POST;
        foreach($params as $key => $value)
        {
            if (!in_array($key, $fields))
            {
                unset($params[$key]);
            }
        }
        $ret = array('errno' => 0, 'errmsg' => 'ok');
        if (!isset($_POST['user']) || !isset($_POST['pwd']) || !isset($_POST['email'])){
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        if (isset($_POST['level']) && $_POST['level'] >= $_SESSION['level']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能创建权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $db = Vera_Database::getInstance();
        $users = $db->select('cms_Admin', 'user');
        $users = array_column($users, 'user');
        if (in_array($_POST['user'], $users))
        {
            echo json_encode(array('errno' => 1, 'errmsg' => '用户名被占用'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $token = md5($_POST['user'] . $_POST['pwd']);
        $params['token'] = $token;
        $db->insert('cms_Admin', $params);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _setPass()
    {
        $required = array('uid', 'isPassed');
        foreach ($required as $item)
        {
            if (!isset($_POST[$item]) || !is_numeric($_POST[$item]))
            {
                echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
                return false;
            }
        }

        $isPassed = $_POST['isPassed'] == 1 ? 1 : 0;
        $db = Vera_Database::getInstance();
        $data = new Data_Admin();
        $level = $data->getAdminById($_POST['uid'])['level'];
        if (empty($level) || $level >= $_SESSION['level']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能操作权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $db->update('cms_Admin', array('isPassed' => $isPassed), array('id' => $_POST['uid']));
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return false;
    }

    /**
     * 重置管理员密码
     * @param   string $user  管理员帐号名
     * @return bool     重置是否成功
     */
    private function _resetPwd($user)
    {
        $ret = array('errno' => 0,'errmsg' => 'OK' );
        $db = Vera_Database::getInstance();

        $cond = array('user'=>$user);
        $result = $db->select('cms_Admin', '*', $cond);
        if (!$result) {
            Vera_Log::addWarning('reset unknown user password '.$user);
            return false;
        }
        $level = $result[0]['level'];
        if ($level >= $_SESSION['level'] && $result[0]['id'] != $_SESSION['id']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能操作权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $token = Library_Auth::generateToken($user,'123456');
        $row = array(
            'token' => $token
            );

        $db->update('cms_Admin',$row,$cond);

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    /**
     * 删除管理员
     * @param   string $user  管理员帐号名
     * @return  bool        删除是否成功
     */
    private function _deleteUser($user)
    {
        $ret = array('errno' => 0,'errmsg' => 'OK' );
        $db = Vera_Database::getInstance();

        $cond = array('user'=>$user);
        $result = $db->select('cms_Admin', '*', $cond);
        $level = $result[0]['level'];
        if ($level >= $_SESSION['level'] && $result[0]['id'] != $_SESSION['id']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能操作权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $result = $db->update('cms_Admin', array('deleted' => 1), $cond);
        if (!$result) {
            $ret = array('errno' => '020202','errmsg' => '删除失败' );
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _update($id, $name, $password)
    {
        $ret = array('errno' => 0,'errmsg' => 'OK' );
        $db = Vera_Database::getInstance();

        $result = $db->select('cms_Admin', '*', array('id'=>$id));
        if (!$result) {
            return false;
        }
        $level = $result[0]['level'];
        if ($level >= $_SESSION['level'] && $result[0]['id'] != $_SESSION['id']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能操作权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $user = $result[0]['user'];
        $row = array();
        if (!empty($name)) {
            $row['nickname'] = $name;
            $_SESSION['name'] = $name;
        }
        if (!empty($password)) {
            $token = Library_Auth::generateToken($user, $password);
            $row['token'] = $token;
        }

        if(isset($_POST['xmu_num']) && !empty($_POST['xmu_num'])) {
            $userInfo = $db->select('vera_User', 'wechat_id', array('xmu_num' => $_POST['xmu_num']));
            if (empty($userInfo)) {
                echo json_encode(array('errno' => 1, 'errmsg' => '修改失败，该学号在微信端尚未绑定厦大帐号'), JSON_UNESCAPED_UNICODE);
                return false;
            }
            $row['xmu_num'] = $_POST['xmu_num'];
            $row['openid'] = array_pop($userInfo)['wechat_id'];
        }


        $db->update('cms_Admin', $row, array('id'=>$id));

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    /**
     * 更新管理员权限
     * @param   string $user   管理员用户名
     * @param   int $level  权限
     * @return  bool         更新是否成功
     */
    private function _updateLevel($user, $level)
    {
        //校验等级合法性
        if ($level >= 10 || $level < 0) {
            return false;
        }
        $ret = array('errno' => 0,'errmsg' => 'OK' );
        $db = Vera_Database::getInstance();
        $cond = array('user'=>$user);

        $result = $db->select('cms_Admin', 'level', $cond);
        //开发者权限不允许通过接口更改
        if (!$result || $result[0]['level'] >= 10) {
            return false;
        }

        $levelDb = $result[0]['level'];
        if ($levelDb >= $_SESSION['level'] || $level >= $_SESSION['level']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能操作权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $row = array('level'=>$level);
        $db->update('cms_Admin', $row, $cond);

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    /**
     * 从配置文件中获取当前各种权限
     * @return bool
     */
    private function _getAllPrivileges()
    {
        $data = new Data_Admin();
        $res = $data->getAllPrivileges();
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $res), JSON_UNESCAPED_UNICODE);
        return true;
    }

    /**
     * 校验参数合法性
     * @param array $required
     * @return bool
     */
    public function checkParam($required = array())
    {
        if (!is_array($required) || empty($required))
            return false;
        foreach ($required as $item => $description)
        {
            if (!isset($_REQUEST[$item]) || empty($_REQUEST[$item]))
            {
                return false;
            }
            if (isset($description['type']) && !is_numeric($_REQUEST[$item]))
            {
                return false;
            }
            if (isset($description['value']) && !in_array($_REQUEST[$item], $description['value']))
            {
                return false;
            }
        }
        return true;
    }

    private function _addPrivilege()
    {
        $data = new Data_Admin();
        $privileges = $data->getAllPrivileges();
        $menus = array_keys($privileges['menus']);
        $sub = array_keys($privileges['subPrivileges']);

        @$required = array('uid' => array('type'), 'privilege' => array('value' => array_merge($menus, $sub)), 'level' => array('type', 'value' => array(1,2)));
        if (!$this->checkParam($required))
        {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $res = $data->getAdminById($_POST['uid']);
        $level = $res['level'];
        if ($level >= $_SESSION['level']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能操作权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }

        $description = '';
        if ($_POST['level'] == 1) {
            foreach($privileges['menus'] as $privilege => $desc) {
                if ($privilege == $_POST['privilege'])
                    $description = $desc;
            }
        } else {
            foreach($privileges['subPrivileges'] as $privilege => $desc) {
                if ($privilege == $_POST['privilege']) {
                    $description = $desc;
                }
            }
        }
        if (empty($description)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $insert = array('uid' => $_POST['uid'], 'privilege' => $_POST['privilege'], 'level' => $_POST['level'], 'deleted' => 0, 'description' => $description);
        $update = array('level' => $_POST['level'], 'deleted' => 0,'description' => $description);
        $db = Vera_Database::getInstance();
        $db->insert('cms_Privilege', $insert, null, $update);
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $db->getInsertId()), JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _removePrivilege()
    {
        $required = array('privilegeId' => array('type'));
        if (!$this->checkParam($required))
        {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return false;
        }

        $db = Vera_Database::getInstance();
        $privilegeLog = $db->select('cms_Privilege', '*', array('id' => $_POST['privilegeId']));
        $data = new Data_Admin();
        $res = $data->getAdminById($privilegeLog[0]['uid']);
        if ($res['level'] >= $_SESSION['level'] && $res['id'] != $_SESSION['id']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能操作权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $db->update('cms_Privilege', array('deleted' => 1), array('id' => $_POST['privilegeId']));
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _getPrivilegeForAdmin()
    {
        $required = array('adminId' => array('type'));
        if (!$this->checkParam($required)) {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return false;
        }

        $data = new Data_Admin();
        $admin = $data->getAdminById($_GET['adminId']);
        if (empty($admin)){
            echo json_encode(array('errno' => 1, 'errmsg' => '管理员不存在'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        if ($admin['level'] >= $_SESSION['level'] && $admin['id'] != $_SESSION['id']){
            echo json_encode(array('errno' => 1, 'errmsg' => '您当前只能查看权限等级小于' . $_SESSION['level'] . '的管理员'), JSON_UNESCAPED_UNICODE);
            return false;
        }
        $adminLevel = $admin['level'];
        $originalPrivileges = $data->getAllPrivileges();
        $privileges = array();
        foreach ($originalPrivileges as $key => $privilege) {
            foreach ($privilege as $name => $description) {
                $privileges[$key][$name]['description'] = $description;
                if ($adminLevel == 10){
                    $privileges[$key][$name]['obtained'] = true;
                } else {
                    $privileges[$key][$name]['obtained'] = false;
                }
            }
        }
        $db = Vera_Database::getInstance();
        $res = $db->select('cms_Privilege', '*', array('deleted' => 0, 'uid' => $_GET['adminId']));
        if (!empty($res)) {
            foreach ($res as $row) {
                if ($row['level'] == 1) {
                    $privileges['menus'][$row['privilege']]['obtained'] = true;
                    $privileges['menus'][$row['privilege']]['id'] = $row['id'];
                } else {
                    $privileges['subPrivileges'][$row['privilege']]['obtained'] = true;
                    $privileges['subPrivileges'][$row['privilege']]['id'] = $row['id'];
                }
            }
        }
        echo json_encode(array('errno' => 0, 'errmsg' => 'ok', 'data' => $privileges), JSON_UNESCAPED_UNICODE);
        return true;
    }
}
