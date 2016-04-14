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
class Action_Api_Admin_Project{

    public function run(){
        if(!isset($_GET['m']) || empty($_GET['m'])){
            return false;
        }

        switch($_GET['m']){
            case 'getProjectList'://pass
                $this->getProjectList();
                break;
            case 'getProjectPage'://pass
                $this->getProjectPage();
                break;
            case 'addProject'://pass
                $this->addProject();
                break;
            case 'addClass'://pass
                $this->addClass();
                break;
            case 'getTeacher'://pass
                $this->getTeacher();
                break;
            case 'addStudent'://pass
                $this->addStudent();
                break;
            case 'addCourse'://pass
                $this->addCourse();
                break;
            case 'deleteClass'://pass
                $this->deleteClass();
                break;
            case 'deleteStudent'://pass
                $this->deleteStudent();
                break;
            case 'deleteCourse'://pass
                $this->deleteCourse();
                break;
        }

        return true;
    }

    private function getProjectList(){
        $service = new Service_Project();
        $result = $service->getProjectList();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(is_bool($result)){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function getProjectPage(){
        $service = new Service_Project();
        $page = $service->getProjectPage();
        if(is_bool($page)){
            $ret = array('errno' => 1, 'errmsg'=>'参数错误');
        }else{
            $ret = array('errno' => 0, 'errmsg'=>$page);
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function addProject(){
        $service = new Service_Project();
        $result = $service->addProject();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(!$result){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function addClass(){
        $service = new Service_Project();
        $result = $service->addClass();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(!$result){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function getTeacher(){
        $service = new Service_Project();
        $result = $service->getTeacher();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(!$result){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function addStudent(){
        $service = new Service_Project();
        $result = $service->addStudent();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(!$result){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function addCourse(){
        $service = new Service_Project();
        $result = $service->addCourse();
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        if(!$result){
           echo json_encode($ret, JSON_UNESCAPED_UNICODE);
           return false;
        }
        $ret = array('errno' => 0, 'errmsg'=>$result);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function deleteClass(){
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        $classId = Library_Share::getRequest('classId', Library_Share::INT_DATA);
        $projectId = Library_Share::getRequest('projectId', Library_Share::INT_DATA);
        if(is_bool($classId) || is_bool($projectId)){
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $db = new Data_Db();
        $project = $db->getProject($projectId);
        if(empty($project)){
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if($project['startTime'] < date('Y-m-d H:i:s')){
            $ret['errmsg'] = '项目正在进行或结束，不能进行删除操作';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if(!$db->deleteClass($classId)){
            $ret['errmsg'] = '删除失败';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        $ret['errno'] = 0;
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function deleteStudent(){
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        $studentId = Library_Share::getRequest('studentId', Library_Share::INT_DATA);
        $projectId = Library_Share::getRequest('projectId', Library_Share::INT_DATA);
        if(is_bool($studentId) || is_bool($projectId)){
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $db = new Data_Db();
        $project = $db->getProject($projectId);
        if(empty($project)){
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if($project['startTime'] < date('Y-m-d H:i:s')){
            $ret['errmsg'] = '项目正在进行或已结束，不能进行删除操作';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if(!$db->deleteStudent($studentId)){
            $ret['errmsg'] = '删除失败';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        $ret['errno'] = 0;
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function deleteCourse(){
        $ret = array('errno'=>1, 'errmsg'=>'参数错误');
        $courseId = Library_Share::getRequest('courseId', Library_Share::INT_DATA);
        if(is_bool($courseId)){
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        $db = new Data_Db();
        $course = $db->getCourse(array('id'=>$courseId));
        if(empty($course)){
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if($course['startTime'] < date('Y-m-d H:i:s')){
            $ret['errmsg'] = '课程正在进行或已结束，不能进行删除操作';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        if(!$db->deleteCourse($courseId)){
            $ret['errmsg'] = '删除失败';
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        $ret['errno'] = 0;
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

}
?>