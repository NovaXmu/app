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
class Action_Project{
    public function run(){
        if(!isset($_GET['m']) || empty($_GET['m'])){
            return false;
        }

        switch($_GET['m']){
            case 'projectList'://pass
                $this->projectList();
                break;
            case 'project'://pass
                $this->project();
                break;
            case 'teacherList'://pass
                $this->teacherList();
                break;
            case 'studentList'://pass
                $this->studentList();
                break;
            case 'courseList'://pass
                $this->courseList();
                break;
        }
        return true;
    }

    private function projectList(){
        $service = new Service_Project();
        $view = new Vera_View(true);
        $view->assign('page', $service->getProjectPage());
        $view->assign('list', $service->getProjectList());
        $view->display('roster/admin/projectList.tpl');
        return true;
    }

    private function project(){
        $service = new Service_Project();
        $view = new Vera_View(true);
        $view->assign('project', $service->getProject());
        $view->display('roster/admin/project.tpl');
        return true;
    }

    private function teacherList(){
        $service = new Service_Project();
        $view = new Vera_View(true);
        $view->assign('list', $service->getClassList());
        $view->display('roster/admin/teacherList.tpl');
        return true;
    }

    private function studentList(){
        $service = new Service_Project();
        $view = new Vera_View(true);
        $list = $service->getStudentList();
        $classList = $service->getClassList(false);
        $view->assign('list', empty($list)?array():$list);
        $view->assign('classList', empty($classList)?array():$classList);
        $view->assign('classId',is_bool(Library_Share::getRequest('classId'))?0:Library_Share::getRequest('classId'));
        $view->display('roster/admin/studentList.tpl');
        return true;
    }

    private function courseList(){
        $service = new Service_Project();
        $classList = $service->getClassList(false);
        $list = $service->getCourseList();
        $view = new Vera_View(true);
        $view->assign('list', empty($list)?array():$list);
        $view->assign('classList', empty($classList)?array():$classList);
        if(is_bool(Library_Share::getRequest('date'))){
            $view->assign('date', date('Y-m-d'));
        }else{
            $view->assign('date', Library_Share::getRequest('date'));
        }
        $view->display('roster/admin/courseList.tpl');
        return true;
    }

}
?>