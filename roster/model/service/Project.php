<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Project.php
*   description:      Action for Project.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Service_Project{
    function __construct(){}

    public function getProject(){
        $projectId = Library_Share::getRequest('id', Library_Share::INT_DATA);
        if(is_bool($projectId)){
            return null;
        }
        $db = new Data_Db();
        return $db->getProject($projectId);
    }

    /**
     * 获取项目列表
     * @return array
     */
    public function getProjectList(){
        $page = Library_Share::getRequest('page', Library_Share::INT_DATA);
        $time = Library_Share::getRequest('time', Library_Share::INT_DATA);

        if(is_bool($page) || is_bool($time) || $page < 1 || $time > 1 || $time < -1){
            return false;
        }

        $db = new Data_Db();
        return $db->getProjectList($page, $time);
    }

    public function getProjectPage(){
        $time = Library_Share::getRequest('time', Library_Share::INT_DATA);
        if($time > 1 || $time < -1){
            return false;
        }
        $db = new Data_Db();
        $count = $db->getProjectCount($time);
        if(is_int($count) && $count != 0){
            return ceil($count/11);
        }
        return 0;
    }

    /**
     * 添加项目
     */
    public function addProject(){
        $name = Library_Share::getRequest('name');
        $introduction = Library_Share::getRequest('introduction');
        $startTime = Library_Share::getRequest('startTime');
        $endTime = Library_Share::getRequest('endTime');

        if(is_bool($name) ||is_bool($introduction) || is_bool($startTime) || is_bool($endTime)){
            return false;
        }
        if($startTime < date('Y-m-d')){
            return false;
        }
        if($startTime >= $endTime){
            return false;
        }
        
        $db = new Data_Db();
        return $db->addProject($name, $introduction, $startTime, $endTime);
    }


    /**
     * 获取班级数组
     * @return array 班级数组
     */
    public function getClassList($hasTeacher = true){
        $projectId = Library_Share::getRequest('projectId', Library_Share::INT_DATA);
        if(is_bool($projectId)){
            return false;
        }
        $db = new Data_Db();
        $classList = $db->getClassList(array('projectId' => $projectId));
        if(is_array($classList)){
            if($hasTeacher)
                for($i = 0; $i < count($classList); $i++)
                    $classList[$i]['teacher'] = $db->getUser(array('id' => $classList[$i]['teacherId']));
        }else{
            $classList = array();
        }
        $list = array();
        foreach($classList as $class){
            $list[$class['id']] = $class;
        }
        return $list;
    }

    /**
     * 添加班级
     */
    public function addClass(){
        $projectId = Library_Share::getRequest('projectId');
        $num = Library_Share::getRequest('num');
        $name = Library_Share::getRequest('name');
        $telephone = Library_Share::getRequest('telephone');
        $email = Library_Share::getRequest('email');
        if(is_bool($projectId) || is_bool($num) || is_bool($name) || is_bool($telephone) || is_bool($email)){
            return false;
        }
        //检查学生excel文件
        if(empty($_FILES['file_stu']['name'])){
            return false;
        }
        $file_types = explode('.', $_FILES['file_stu']['name']);
        $file_type = $file_types[count($file_types)-1];
        if(strtolower($file_type) != 'xls'){
            return false;
        }

        //按需添加老师、班级、学生
        $db = new Data_Db();
        $rows = array(
            'name' => $name,
            'telephone' => $telephone,
            'email' => $email,
            'type' => 2);
        $teacherId = $db->addUser($rows);
        if($teacherId == 0){
            return false;
        }
        $classId = $db->addClass($projectId, $teacherId, $num);
        if($classId == 0){
            return false;
        }
        return $this->addStudentByType($_FILES['file_stu'], $classId);
    }

    /**
     * 根据电话号码获取班主任信息
     * @return array
     */
    public function getTeacher(){
        $telephone = Library_Share::getRequest('telephone');
        if(is_bool($telephone)){
            return false;
        }
        $db = new Data_Db();
        return $db->getUser(array('telephone' => $telephone, 'type' => 2));
    }

    /**
     * 获取学生列表
     * @return array [description]
     */
    public function getStudentList(){
        $classId = Library_Share::getRequest('classId', Library_Share::INT_DATA);
        $projectId = Library_Share::getRequest('projectId', Library_Share::INT_DATA);
        if((is_bool($classId) && is_bool($projectId))){
            return false;
        }
        if($classId == 0 && $projectId == 0){
            return false;
        }
        $db = new Data_Db();
        return $db->getStudentList($projectId, $classId);
    }

    public function addStudent(){
        $classId = Library_Share::getRequest('classId', Library_Share::INT_DATA);
        $name = Library_Share::getRequest('name');
        $telephone = Library_Share::getRequest('telephone');
        $email = Library_Share::getRequest('email');
        if(is_bool($classId) || is_bool($name) || is_bool($telephone) || is_bool($email)){
            return false;
        }
        return $this->addStudentByType(
            array(
                'name' => $name,
                'telephone' => $telephone,
                'email' => $email,
                'type' => 1
                ),
            $classId, false);
    }

    /**
     * 获取课程数组
     * @return array 课程数组
     */
    public function getCourseList(){
        $projectId = Library_Share::getRequest('projectId', Library_Share::INT_DATA);
        $date = Library_Share::getRequest('date');
        if(is_bool($projectId)){
            return false;
        }
        if(is_bool($date)){
            $date = date('Y-m-d');
        }
        $db = new Data_Db();
        $list = $db->getCourseList($projectId, $date);
        for($i = 0; $i < count($list); $i++){
            $list[$i]['start'] = substr($list[$i]['startTime'], 10);
            $list[$i]['end'] = substr($list[$i]['endTime'], 10);
        }
        return $list;
    }

    public function addCourse(){
        $projectId = Library_Share::getRequest('projectId', Library_Share::INT_DATA);
        $name = Library_Share::getRequest('name');
        $teacherName = Library_Share::getRequest('teacherName');
        $address = Library_Share::getRequest('address');
        $startTime = Library_Share::getRequest('startTime');
        $endTime = Library_Share::getRequest('endTime');
        $type = Library_Share::getRequest('type', Library_Share::INT_DATA);
        $classes = Library_Share::getRequest('classes');
        if(is_bool($projectId) || is_bool($name) || is_bool($teacherName) || is_bool($address) || is_bool($startTime) || is_bool($endTime) || is_bool($type)){
            return false;//参数错误
        }
        if($type == 1){
            if(is_bool($classes)){
                return false;
            }
        }
        if($startTime < date('Y-m-d H:i:s')){
            var_dump('1');
            return false;//开始时间小于当前时间
        }
        if($startTime > $endTime){
            var_dump('2');
            return false;//开始时间大于结束时间
        }
        if($type != 1 && $type != 2){
            return false;//课程类型有误
        }
        $rows = array('projectId' => $projectId,
            'name' => $name,
            'teacherName' => $teacherName,
            'address' => $address,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'type' => $type,
            'classes' => $classes);
        $db = new Data_Db();
        $project = $db->getProject($projectId);
        if($project['startTime']<=$rows['startTime'] && $project['endTime'] >= $rows['endTime'])
            return $db->addCourse($rows);
        return false;
    }

    /**
     * 批量导入或者单独添加学生
     * @param array/file  $data    数据，file或者数组
     * @param int  $classId 班级ID
     * @param boolean $isFile  是否是文件 true 是文件
     */
    private function addStudentByType($data, $classId, $isFile = true){
        if($isFile){
            $file_types = explode('.', $data['name']);
            $file_type = $file_types[count($file_types)-1];
            if(strtolower($file_type) != 'xls'){
                return false;
            }
            $savePath = SERVER_ROOT . 'app/roster/library/';
            $file_name = date('Ymdhis') . '.' . $file_type;
            if(!copy($data['tmp_name'], $savePath.$file_name)){
                return false;
            }

            $excel = new Library_Excel();
            $data = $excel->read($savePath.$file_name);
            $db = new Data_Db();
            for($i = 2; $i <= count($data); $i++){
                $rows = array(
                'name' => $data[$i][0],
                'telephone' => $data[$i][1],
                'email' => $data[$i][2],
                'type' => 1
                );
                $userId = $db->addUser($rows);
                if($userId > 0){
                    $db->addStudent($userId, $classId);
                }
            }

            if(!unlink($savePath.$file_name)){
                var_dump('删除失败');
            }
        }else{
            $db = new Data_Db();
            $userId = $db->addUser($data);
            if($userId == 0){
                return false;
            }
            $stu = $db->addStudent($userId, $classId);
            if($stu == 0){
                return false;
            }
        }
        return true;
    }
}
?>