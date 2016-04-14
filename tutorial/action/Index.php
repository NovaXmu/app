<?php
/**
*
* @copyright 
*
* file:			Index.php
* description:	投票主页
*
* @author linjun
**/

/**
* 抢票页
*/
class Action_Index extends Action_Base
{

	function __construct($resource)
	{
		parent::__construct($resource);
	}

	public function run(){
        if(!isset($_GET['m'])){
            return $this->_getNowAct();
        }
        else{
            switch($_GET['m']){
                case 'actnow':
                    return $this->_getNowAct();
                    break;
                case 'actover':
                    return $this->_getOverAct();
                    break;
            }
        }
	}

    private function _getNowAct(){
        //获取正在进行的投票活动列表信息
        $resource = $this->getResource();
        $service = new Service_Auction($resource);
        $ret = array(
            'errno' => 0,
            'errmsg' => 'OK',
            'data' => array()
            );
        try{
            $list = $service->getNowActList();
            $ret['data'] = $list;
        }catch(Exception $e){
            $ret = array(
                'errno' => $e->getCode(),
                'errmsg' => $e->getMessage()
                );
        }

        // $view = new Vera_View(true);//设置为true开启debug模式
        // $view->assign('title','投票平台');
        // $view->assign('list', $ret);

        // $view->dailyBackground();
        // $view->display('tutorial/Index.tpl',$actID);
        // return true;
    }

    private function _getOverAct(){
        $resource = $this->getResource();
        //获取正在进行的投票活动列表信息
        $service = new Service_Auction($resource);
        $ret = array(
            'errno' => 0,
            'errmsg' => 'OK',
            'data' => array()
            );
        try{
            $list = $service->getOverActList();
            $ret['data'] = $list;
        }catch(Exception $e){
            $ret = array(
                'errno' => $e->getCode(),
                'errmsg' => $e->getMessage()
                );
        }

        // $view = new Vera_View(true);//设置为true开启debug模式
        // $view->assign('title','投票平台');
        // $view->assign('list', $ret);

        // $view->dailyBackground();
        // $view->display('tutorial/Index.tpl',$actID);
        // return true;
    }
}

?>
