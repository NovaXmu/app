<?php
/**
*
*   @copyright  Copyright (c) 2016 echo Lin
*   All rights reserved
*
*   file:             Wechat.php
*   description:      Action for Wechat.php
*
*   @author Echo
*   @license Apache v2 License
*
**/
class Action_Wechat{
    public function run(){
        if(!isset($_GET['m']) || empty($_GET['m'])){
            $m = 'projectList';
        }else{
            $m = $_GET['m'];
        }

        switch($m){
            case 'linkin'://pass
                $this->linkin();
                break;
            case 'projectList'://pass
                $this->projectList();
                break;
            case 'courseList':
                $this->courseList();
                break;
            case 'course':
                $this->course();
                break;
            case 'checkLog':
                $this->checkLog();
                break;
            default:
        }
        return true;
    }

    private function linkin(){
        $view = new Vera_View(true);
        $view->assign('openid', $_SESSION['openid']);
        $view->display('roster/wechat/linkin.tpl');
        return true;
    }

    private function projectList(){
        $service = new Service_Wechat();
        $list = $service->getProjectList();
        $view = new Vera_View(true);
        $view->assign('list', $list);
        $view->display('roster/wechat/projectList.tpl');
        return true;
    }

    private function courseList(){
        $projectId = Library_Share::getRequest('projectId');
        if(is_bool($projectId)){
            return false;
        }
        $date = Library_Share::getRequest('date');
        if(is_bool($date)){
            $date = date('Y-m-d');
        }
        $service = new Service_Wechat();
        $list = $service->getCourseList($date, $projectId);
        $view = new Vera_View(true);
        $view->assign('date', $date);
        $view->assign('projectId', $projectId);
        $view->assign('list', $list);
        $view->assign('user', $_SESSION['user']);
        $view->display('roster/wechat/courseList.tpl');
        return true;
    }

    private function course(){
        if($_SESSION['user']['type'] != 2){
            return false;
        }
        $courseId = Library_Share::getRequest('courseId', Library_Share::INT_DATA);
        if(is_bool($courseId)){
            return false;
        }
        $service = new Service_Wechat();
        $course = $service->getCourse($courseId);
        $view = new Vera_View(true);
        $view->assign('course', $course);
        $view->display('roster/wechat/course.tpl');
        return true;
    }

    private function checkLog(){
        $courseId = Library_Share::getRequest('courseId');
        if(is_bool($courseId))
            return false;
        $service = new Service_Wechat();
        $list = $service->getCheckLog($courseId);
        $course = $service->getCourse($courseId);
        $view = new Vera_View('true');
        $view->assign('list', $list);
        $view->assign('course', $course);
        $view->display('roster/wechat/checkLog.tpl');
        return true;
    }

}
?>