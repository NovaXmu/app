<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Wechat.php
*   description:      Service for Wechat.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Service_Wechat{
    function __construct(){}

    public function getProjectList(){
        $db = new Data_Db();
        if($_SESSION['user']['type'] == 1){//学生
            $classes = $db->getStuClassList(array('studentId' => $_SESSION['user']['id']));
        }else if($_SESSION['user']['type'] == 2){//老师
            $classes = $db->getClassList(array('teacherId' => $_SESSION['user']['id']));
        }
        if(empty($classes)){
            return array();
        }
        $conds = 'id in (';
        foreach($classes as $class)
            $conds .= $class['projectId'] . ',';
        $conds .= '0) and ';
        return $db->getProjectListForWechat($conds);
    }

    public function getCourseList($date, $projectId){
        $db = new Data_Db();
        if($_SESSION['user']['type'] == 1){//学生
            $class = $db->getStuClassList(array('studentId' => $_SESSION['user']['id'], 'projectId' => $projectId));
            if(empty($class))
                return array();
            $class = $class[0]['classId'];
        }else if($_SESSION['user']['type'] == 2){//老师
            $class = $db->getClassList(array('teacherId' => $_SESSION['user']['id'], 'projectId' => $projectId));
            if(empty($class))
                return array();
            $class = $class[0]['id'];
        }
        $list = $db->getCourseList($projectId, $date, $class);
        for($i = 0; $i < count($list); $i++){
            $list[$i]['start'] = substr($list[$i]['startTime'], 10, 6);
            $list[$i]['end'] = substr($list[$i]['endTime'], 10, 6);
        }
        if($_SESSION['user']['type'] == 1){
            $conds = array(
                'studentId' => $_SESSION['user']['id'],
                'courseId' => ''
                );
            for($i = 0; $i < count($list); $i++){
                $conds['courseId'] = $list[$i]['id'];
                $log = $db->getCheckLog($conds);
                if(empty($log)){
                    $list[$i]['check'] = 0;//未点名
                }else if(empty($log[0]['checkTime'])){
                    $list[$i]['check'] = -1;//未签到
                }else{
                    $list[$i]['check'] = 1;//已签到
                }
            }
        }else{
            $conds = array('courseId' => '');
            for($i = 0; $i < count($list); $i++){
                $conds['courseId'] = $list[$i]['id'];
                $log = $db->getCheckLog($conds);
                if(empty($log)){
                    $list[$i]['check'] = 0;//未点名
                }else{
                    $list[$i]['check'] = 1;//已点名
                }
            }
        }
        return $list;
    }

    public function getCourse($courseId){
        $db = new Data_Db();
        $course = $db->getCourse(array('id' => $courseId));
        $now = date('Y-m-d H:i:s');
        if($course['endTime'] < $now){
            $course['time'] = -1;
        }else{
            $course['time'] = 1;
        }
        return $course;
    }

    public function getCheckLog($courseId){
        $db = new Data_Db();
        $log = $db->getCheckLog(array('courseId' => $courseId));
        $list = array(
            'total' => count($log),
            'count' => 0,
            'checkin' => array(),
            'notCheckin' => array()
            );
        foreach($log as $checklog){
            $user = $db->getUser(array('id' => $checklog['studentId']));
            $checklog['user'] = $user;
            if(empty($checklog['checkTIme']))
                $list['notCheckin'][] = $checklog;
            else
                $list['checkin'][] = $checklog;
        }
        $list['count'] = count($list['checkin']);
        return $list;

    }

    public function startCheck(){
        $courseId = Library_Share::getRequest('courseId');
        if(is_bool($courseId)){
            return false;
        }
        $db = new Data_Db();
        $course = $db->getCourse(array('id' => $courseId));
        if(empty($course))
            return false;
        if($course['endTime'] < date('Y-m-d H-i-s'))
            return false;
        if($course['isStart'] == -1){
            $this->initCourse($course);
            $db->setCourse($courseId, array('isStart' => 1));
        }
        return $courseId . '|' . $db->newCode($courseId);
    }

    public function checkIn($courseId, $code){
        $db = new Data_Db();
        $list = array('course'=>'', 'message'=>'');
        $list['course'] = $db->getCourse(array('id' => $courseId));
        $now = date('Y-m-d H:i:s');
        if($list['course']['endTime'] < $now){
            $list['message'] = '错过了签到时间';
        }else if(empty($list['course']['code'])){
            $list['message'] = '还没有开始签到';
        }else if($list['course']['code'] != $code){
            $list['message'] = '签到码错误，请重新签到';
        }else{
            $conds = array(
                'courseId' => $courseId,
                'studentId' => $_SESSION['user']['id']
                );
            $log = $db->getCheckLog($conds);
            if(empty($log))
                $list['message'] = '您没有参与该课程不能签到';
            else if(!empty($log[0]['checkTime']))
                $list['message'] = '已经签到';
            else{
                $conds = array('id' => $log[0]['id']);
                $rows = array('checkTime' => date('Y-m-d H:i:s'));
                if($db->setCheckLog($conds, $rows))
                    $list['message'] = '签到成功';
                else
                    $list['message'] = '签到失败,请稍后重试';
            }

        }
        return $list;
    }

    private function initCourse($course){
        $classes = explode('-', $course['classes']);
        $db = new Data_Db();
        $conds = 'classId in (';
        foreach($classes as $class)
            $conds .= $class . ',';
        $conds .= '0)';
        $stuList = $db->getStuClassList($conds);
        $rows = array('courseId' => $course['id']);
        foreach($stuList as $stu){
            $rows['studentId'] = $stu['studentId'];
            $db->addCheckLog($rows);
        }
    }
}
?>