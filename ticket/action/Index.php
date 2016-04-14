<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			text.php
*	description:	抢票页
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 抢票页
*/
class Action_Index extends Action_Base
{

    public function run()
    {

        header('location: /templates/ticket/dist/index.html');
        exit();
    }
}
        /*$resource = $this->getResource();
        $actID = $resource['actID'];

        $model = new Service_Info($resource);

        $view = new Vera_View(true);//设置为true开启debug模式
        $view->setCacheLifetime( -1 );//缓存永不过期

        $isCached = $view->isCached('ticket/Index.tpl', $actID);//每个活动生成一个缓存，永不过期
        Vera_Log::addNotice('isCached', intval($isCached));
        if (!$isCached) {
            //获取抢票活动内容
            $info = $model->getInfo();
            if (isset($info['errmsg']) && $info['errmsg'] == '活动不存在')
            {
                return false;
            }
            $view->assign('title','抢票平台');
            $view->assign('id', $actID);//活动id
            $view->assign('name', $info['name']);//活动名称
            $view->assign('content', $info['content']);//活动详情
            //$view->assign('total', $info['total']);//总票数
            //$view->assign('times', $info['times']);//每人可抢的次数
            $view->assign('startTime', $info['startTime']);
            $view->assign('endTime', $info['endTime']);
            $view->assign('demand', $info['demand']);//抢票需求，click为点击按钮抢票
            //$view->assign('chance', $info['chance'] * 100 . '%');//概率

            $view->assign('indexPic','/templates/ticket/img/cover.jpg');//首页大图
            $temp = array('/templates/ticket/img/pic.jpg','/templates/ticket/img/cover.jpg','/templates/ticket/img/brand.png');
            //$view->assign('picArr', $temp);
            $temp = array('x'=>'118.0983296896','y'=>'24.4370561971');
            $view->assign('location', $temp);
        }


        $userInfo = $model->getUserTicket();//检查是否已抢到票
        if ($userInfo) {
            $view->assign('result', $userInfo['result']);
            $view->assign('token', $userInfo['token']);
        }
        else {
            $view->assign('result', 0);

            $left = $model->getLeftTicket();//余票个数
            $view->assign('leftTicket', $left);

            $left = $model->getLeftChance();//剩余抢票次数
            $view->assign('leftChance', $left);
        }
        $view->assign('openid', $resource['openid']);//动态赋值openid

        $view->dailyBackground();
        $view->display('ticket/Index.tpl',$actID);
        return true;
	}
}

?>*/

