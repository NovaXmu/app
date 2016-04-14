<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Db.php
*   description:      Action for Db.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Data_Db{

    function __construct(){}

    /**
     * 根据条件获取用户
     * @param  array $conds 条件
     * @return array        用户
     */
    public function getUser($conds){
        $db = Vera_Database::getInstance();
        $ret = $db->select('roster_User', '*', $conds);
        if(isset($ret[0])){
            return $ret[0];
        }else{
            return array();
        }
    }

    /**
     * 更新用户数据
     * @param array $conds [description]
     * @param array $rows  [description]
     */
    public function setUser($conds, $rows){
        $db = Vera_Database::getInstance();
        return $db->update('roster_User', $rows, $conds);
    }

    /**
     * 添加用户
     * @param array $data 数据
     */
    public function addUser($data){
        $user = $this->getUser($data);
        if(!empty($user)){
            return $user['id'];
        }
        $db = Vera_Database::getInstance();
        return $db->insert('roster_User', $data);
    }

    /**
     * 是否是管理员
     * @param  int  $account  账户
     * @param  String  $password 密码
     * @return array    
     */
    public function isManager($account, $password){
        $db = Vera_Database::getInstance();
        return $db->select('roster_Manager', '*', array('account' => $account, 'password' => $password, 'isUse' => 1));
    }

    /**
     * 获取管理员数组
     * @param  int $page 页数
     * @return array      
     */
    public function getManagerList($page){
        $db = Vera_Database::getInstance();
        // $appends = 'limit ' . ($page-1)*10 .', 10';
        // return $db->select('roster_Manager', '*', NULL, NULL, $appends);
        return $db->select('roster_Manager', '*');
    }

    /**
     * 添加管理员
     * @param string  $account   登录账户
     * @param string  $nickname  昵称
     * @param string $password  密码
     * @param int $privilege 权限
     */
    public function addManager($array){
        $array['isUse'] = 1;
        $db = Vera_Database::getInstance();
        return $db->insert('roster_Manager', $array);
    }

    /**
     * 修改管理员信息
     * @param  int $id  管理员id
     * @param  array $arr 信息数组
     * @return boolean      
     */
    public function modifyManager($id, $arr){
        $db = Vera_Database::getInstance();
        return $db->update('roster_Manager', $arr, array('id'=>$id));
    }

    /**
     * 根据项目id获取项目详情
     * @param  int $id 项目id
     * @return array     项目详情
     */
    public function getProject($id){
        $db = Vera_Database::getInstance();
        $ret = $db->select('roster_Project', '*', array('id' => $id));
        if(empty($ret)){
            return array();
        }
        return $ret[0];
    }

    /**
     * 获取项目列表
     * @param  int $page 页码
     * @param  int $time 时间（0：正在进行 1：即将开始 -1：已结束）
     * @return array       
     */
    public function getProjectList($page, $time){
        $db = Vera_Database::getInstance();
        $now = date('Y-m-d');
        switch($time){
            case 0://正在进行的项目
                $conds = 'startTime <= "' . $now . '" and endTime >= "' . $now . '"';
                break;
            case 1://即将开始的项目
                $conds = 'startTime > "'. $now . '"';
                break;
            case -1://已结束的项目
                $conds = 'endTime < "' . $now . '"';
                break;
            default:
                return array();
        }
        $appends = 'order by id desc limit ' . ($page-1)*11 .', 11';
        $data = $db->select('roster_Project', '*', $conds, NULL, $appends);
        //var_dump($db->getLastSql());
        return $data;
    }

    public function getProjectListForWechat($conds){
        $db = Vera_Database::getInstance();
        $now = date('Y-m-d');
        $conds .= ' startTime <= "' . $now . '"';
        $appends = 'order by startTime desc';
        return $db->select('roster_Project', '*', $conds, NULL, $appends);
    }

    /**
     * 根据时间获取项目总数
     * @param  int $time 时间
     * @return array       总数
     */
    public function getProjectCount($time){
        $db = Vera_Database::getInstance();
        $now = date('Y-m-d');
        switch($time){
            case 0://正在进行的项目
                $conds = 'startTime <= "' . $now . '" and endTime >= "' . $now . '"';
                break;
            case 1://即将开始的项目
                $conds = 'startTime > "'. $now . '"';
                break;
            case -1://已结束的项目
                $conds = 'endTime < "' . $now . '"';
                break;
            default:
                return 0;
        }
        return $db->selectCount('roster_Project', $conds);
    }

    /**
     * 添加项目
     * @param String $name       项目名称
     * @param String $introduction 项目介绍
     * @param string $startTime 项目开始时间
     * @param string $endTime   项目结束时间
     */
    public function addProject($name, $introduction, $startTime, $endTime){
        $array = array(
            'name' => $name,
            'introduction' => $introduction,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'time' => date('Y-m-d')
            );
        $db = Vera_Database::getInstance();
        return $db->insert('roster_Project', $array);
    }

    public function getClassList($conds){
        $db = Vera_Database::getInstance();
        // $appends = 'order by num limit ' . ($page-1)*10 .', 10';
        // return $db->select('roster_Class', '*', array('projectId' => $projectId), NULL, $appends);
        return $db->select('roster_Class', '*', $conds);
    }


    /**
     * 添加班级
     * @param int $projectId 项目ID
     * @param int $teacherId 老师ID
     * @param int $num       班级编号
     */
    public function addClass($projectId, $teacherId, $num){
        $db = Vera_Database::getInstance();
        $row = array(
            'projectId' => $projectId,
            'teacherId' => $teacherId,
            'num' => $num
            );
        return $db->insert('roster_Class', $row);
    }

    /**
     * 根据条件检索班级
     * @param  array $conds 条件
     * @return array        班级数组
     */
    public function getClass($conds){
        $db = Vera_Database::getInstance();
        $ret = $db->select('roster_Class', '*', $conds);
        if(is_bool($ret)){
            return NULL;
        }
        return $ret[0];
    }

    public function deleteClass($classId){
        $db = Vera_Database::getInstance();
        $conds = array('classId' => $classId);
        $db->delete('roster_StuClass', $conds);
        return $db->delete('roster_Class', array('id' => $classId));
    }

    /**
     * 根据项目ID和班级ID获取学生列表
     * @param  int $projectId 项目ID
     * @param  int $classId   班级ID
     * @return array            学生数组
     */
    public function getStudentList($projectId, $classId){
        $db = Vera_Database::getInstance();
        if(is_int($classId) && $classId != 0){
            $ret = $db->select('roster_Class', '*', array('id' => $classId, 'projectId' => $projectId));
            if(!isset($ret)){
                return array();
            }
            $studentList = $db->select('roster_StuClass', '*', array('classId' => $classId));
        }else{
            $ret = $db->select('roster_Class', '*', array('projectId'=> $projectId));
            $conds = 'classId in (';
            foreach($ret as $class)
                $conds .= $class['id'] . ',';
            $conds .='-1)';
            $studentList =  $db->select('roster_StuClass', '*', $conds);
        }

        for($i = 0; $i<count($studentList); $i++){
            $studentList[$i]['user'] = $this->getUser(array('id'=>$studentList[$i]['studentId']));
        }
        return $studentList;
    }

    public function getStuClassList($conds){
        $db = Vera_Database::getInstance();
        return $db->select('roster_StuClass', '*', $conds);
    }

    /**
     * 根据项目和日期获取课程数组
     * @param  int $projectId 项目ID
     * @param  String $date      日期
     * @return array            课程数组
     */
    public function getCourseList($projectId, $date, $class = NULL){
        $db = Vera_Database::getInstance();
        $conds = 'projectId = ' . $projectId . ' and startTime like "' . $date . '%"';
        if($class != NULL){
            $conds .= " and ( classes like '%-$class-%' or classes like '%-$class' or classes like '$class-%' )";
        }
        $appends = 'order by startTime';
        return  $db->select('roster_Course', '*', $conds, NULL, $appends);
    }

    /**
     * 添加课程
     * @param   $rows 包括以下参数
     * @param int $projectId   项目id
     * @param String $name        课程名称
     * @param String $teacherName 授课老师
     * @param String $address     上课地址
     * @param String $startTime   上课时间
     * @param String $endTime     下课时间
     * @param int $type        课程类型 1普通 2兴趣
     * @param String $classes     上课的班级
     */
    public function addCourse($rows){
        $db = Vera_Database::getInstance();
        $ret = $db->insert('roster_Course', $rows);
        return $ret;
    }

    public function setCourse($courseId, $rows){
        $db = Vera_Database::getInstance();
        $conds = array('id' => $courseId);
        return $db->update('roster_Course', $rows, $conds);
    }

    public function getCourse($conds){
        $db = Vera_Database::getInstance();
        $ret = $db->select('roster_Course', '*', $conds);
        if(empty($ret)){
            return array();
        }else{
            return $ret[0];
        }
    }

    public function deleteCourse($courseId){
        $db = Vera_Database::getInstance();
        return $db->delete('roster_Course', array('id' => $courseId));
    }

    /**
     * 为班级添加学生
     * @param int $studentId 学生ID
     * @param int $classId   班级ID
     */
    public function addStudent($studentId, $classId){
        $db = Vera_Database::getInstance();
        $class = $this->getClass(array('id' => $classId));
        return $db->insert('roster_StuClass', array('studentId' => $studentId, 'classId' => $classId, 'projectId' =>$class['projectId']));
    }

    public function deleteStudent($studentId){
        $db = Vera_Database::getInstance();
        return $db->delete('roster_StuClass', array('id'=>$studentId));
    }

    public function getCheckLog($conds){
        $db = Vera_Database::getInstance();
        return $db->select('roster_CheckLog', '*', $conds);
    }

    public function setCheckLog($conds, $rows){
        $db = Vera_Database::getInstance();
        return $db->update('roster_CheckLog', $rows, $conds);
    }

    public function addCheckLog($rows){
        $db = Vera_Database::getInstance();
        return $db->insert('roster_CheckLog', $rows);
    }

    public function newCode($courseId)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'course_' . $courseId . '_code';
        $code = '';
        for ($i = 0; $i < 12; $i++) {
            $code .= chr(mt_rand(48, 122));
        }
        $code = md5($code);
        $cache->add($key, $code, 15);
        if ($cache->getResultCode() == Memcached::RES_NOTSTORED) {
            $code = $cache->get($key);
        }
        Vera_Log::addNotice('code',$code);
        $db = Vera_Database::getInstance();
        $db->update('roster_Course', array('code' => $code), array('id' => $courseId));
        return $code;
    }


}
?>