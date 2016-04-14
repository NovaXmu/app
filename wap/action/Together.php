<?php
/**
*
*	@copyright  Copyright (c) 2015 nidaren
*	All rights reserved
*
*	file:			Auth.php
*	description:	同路人
*
*	@author nidaren
*	@license Apache v2 License
*
**/


class Action_Together extends Action_Base
{
    public $num;
    public $name;
	function __construct (){
        $this->num = $_SESSION['yb_user_info']['yb_studentid'];
        $this->name = $_SESSION['yb_user_info']['yb_realname'];
    }

	public function run()
    {
        if (!isset($_GET['m'])) {
            return $this->_update();//默认显示插入页面
        }
        switch ($_GET['m']) {
            case 'view'://展示匹配页面
                return $this->_view();
                break;
            case 'insert'://执行插入操作
                return $this->_insert();
                break;
            case 'update':
                return $this->_update();
            case 'logout':
                unset($_SESSION["num"]);
                unset($_SESSION["pwd"]);
                break;
            default:
                return $this->_update();
                break;
        }
        return true;
	}


    private function _update()
    {
        $view = new Vera_View(true);
//        $view->setCacheLifetime( -1 );//永久缓存


        $info = Data_Together::getInfo($this->num);

        //根据Data层返回数组中result是否为真判断是否插入过
        if (empty($info)) {
            $info = array(
                'num' => $this->num,
                'name' => $this->name,
                'contact' => '',
                'depart_date_date' => '',
                'depart_date_time' => '',
                'depart_place' => '',
                'arrive_place' => '',
                'waitingtime' => '',
                'pc' => '0'
            );
        } else {
            $info['num'] = $this->num;
            $info['name'] = $this->name;
        }
            $view->assign("data", $info, true);
            $view->display('wap/together/Insert.tpl');
        return true;
    }


    /**
     * 展示页面，如果有插入记录，则显示当前GET参数表示的列表，如果没有记录则展示插入页面
     * @param  boolean $insert 显示插入页面
     * @return bool        无意义
     */
    private function _view($insert = false)
    {

        $view = new Vera_View(true);
//        $view->setCacheLifetime( -1 );//永久缓存


        $info = Data_Together::getInfo($this->num);

        //根据Data层返回数组中result是否为真判断是否插入过
        if (empty($info)) {
            $info = array(
                'num' => $this->num,
                'name' => $this->name,
                'contact' => '',
                'depart_date_date'  => '',
                'depart_date_time' => '',
                'depart_place' => '',
                'arrive_place' => '',
                'waitingtime'  => '',
                'pc'  => '0'
            );
            $view->assign("data", $info, true);
            $view->display('wap/together/Insert.tpl');
        }
        else {
            if (!isset($_POST["depart_place"]) || !isset($_POST["arrive_place"]) || !isset($_POST["depart_date_date"]) || !isset($_POST["depart_date_time"]) || !isset($_POST["waitingtime"])) {
                //若没带参数，则显示当前用户的同行人
                $from = $info["depart_place"];
                $to = $info["arrive_place"];
                $when = $info["depart_date"];
                //$wait = ($info["waitingtime"] - $info["depart_date"])/60;
                $wait = date('Y-m-d H:i:s',(strtotime($when) + $info["waitingtime"]*60));
            }
            else {
                $from = $_POST["depart_place"];
                $to = $_POST["arrive_place"];
                $when = $_POST["depart_date_date"] . " " . $_POST["depart_date_time"];
                //$wait = $_POST["waitingtime"];

                $wait = date('Y-m-d H:i:s',(strtotime($when) + $_POST["waitingtime"]*60));
            }
            $list = Data_Together::getList($from, $to, $when, $wait);
            $view->assign("route", $info,true);
            $view->assign("data", $list,true);

            $view->display("wap/together/View.tpl");
        }
        return true;
    }

	private function _insert()
	{
        $required = array('depart_date_date', 'depart_date_time', 'waitingtime', 'contact', 'depart_place', 'arrive_place');
        foreach ($required as $item)
        {
            if (!isset($_POST[$item]) || empty($_POST[$item]))
            {
                echo json_encode(array('errno' => 1, 'errmsg' => '缺少必要参数'), JSON_UNESCAPED_UNICODE);
                return false;
            }
        }
        $depart_date = $_POST["depart_date_date"] . " " . $_POST["depart_date_time"];
        if (!strtotime($depart_date) || !is_numeric($_POST['waitingtime']) || !is_numeric($_POST['contact']) || strlen($_POST['contact']) != 11)
        {
            echo json_encode(array('errno' => 1, 'errmsg' => '参数非法'), JSON_UNESCAPED_UNICODE);
            return false; 
        }
        $waitingtime = strtotime($depart_date) + $_POST["waitingtime"]*60;
        $waitingtime = date("Y-m-d H:i:s",$waitingtime);
        $pc = isset($_POST["pc"]) ? 1 : 0;
        Data_Together::insert($this->num, $this->name, $_POST["contact"], $depart_date, $_POST["depart_place"], $_POST["arrive_place"], $waitingtime, $pc);

        echo json_encode(array('errno' => 0, 'errmsg' => 'ok'), JSON_UNESCAPED_UNICODE);
        return true;
	}
}

?>
