<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Blog.php
*   description:      部落
*
*   @author Linjun
*   @license Apache v2 License
*
**/

class Action_Blog extends Action_Base{
	function __construct($resource){
		 parent::__construct($resource);
	}

	public function run(){
		$m = Library_Share::getRequest('m');
		if(is_bool($m)){
			return false;
		}
		switch($m){
			case 'blog'://test pass
				$this->getBlogList();
				break;
			case 'blogMember'://test pass
				$this->getBlogMember();
				break;
			case 'activity'://test pass
				$this->getActivity();
				break;
			case 'addActivity'://test pass
				$this->addActivity();
				break;
			case 'activityMember'://test pass
				$this->getActivityMember();
				break;
			case 'test'://更新部落、用户积分情况
				$this->test();
				break;
		}
		return true;
	}

	public function getBlogList(){

		$isMine = Library_Share::getRequest('isMine', Library_Share::INT_DATA);
		if(is_int($isMine)){
			$list = Service_Blog::getBlogList(true);
			//echo '<br/><br/> MyBlogList <br/>';
		}else{
			$list = Service_Blog::getBlogList();
			//echo '<br/><br/> AllBlogList <br/>';
		}

		//var_dump($list);
		$view = new Vera_View(true);//开启debug模式
		$view->assign('list', $list);
		$view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
		$view->display('meet/BlogLists.tpl');
		return true;
	}

	public function getBlogMember(){

		$blog_id = Library_Share::getRequest('blog_id', Library_Share::INT_DATA);
		if(!is_bool($blog_id) && !$blog_id){
			return false;
		}

		$data = Service_Blog::getBlogMemberList($blog_id);
		// echo '<br/><br/> BlogMemberList <br/>';
		// var_dump($data);

		$view = new Vera_View(true);//开启debug模式
		$view->assign('title', $data['title']);
		//$view->assign('blog_id', $data['blog_id']);
		$view->assign('list',$data['list']);
		$view->assign('user',$_SESSION['yb_user_info']['yb_userid']);
		$view->display('meet/BlogMember.tpl');
		return true;
	}

	public function getActivity(){

		$blog_id = Library_Share::getRequest('blog_id', Library_Share::INT_DATA);
		if(!is_bool($blog_id) && !$blog_id){
			return false;
		}

		//是否是查询我发起的活动(只要有isHost参数，不管是什么值，$isHost = true)
		$isHost = Library_Share::getRequest('isHost', Library_Share::INT_DATA);
		if(is_int($isHost)){
			$isHost = true;
		}

		//是否时查询我加入的活动(只要有isJoined参数，不管是什么值，$isJoined = true)
		$isJoined = Library_Share::getRequest('isJoined', Library_Share::INT_DATA);
		if(is_int($isJoined)){
			$isJoined = true;
		}

		$data = Service_Blog::getBlogActivity($blog_id, $isHost, $isJoined);
		// echo '<br/><br/> BlogActivityList <br/>';
		// var_dump($data);

		$view = new Vera_View(true);//开启debug模式
		$view->assign('title', $data['title']);
		$view->assign('list', $data['list']);
		$view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
		$view->assign('nickname', $_SESSION['yb_user_info']['yb_username']);
		$view->display('meet/Blog.tpl');
		return true;
	}

	private function addActivity(){
		$blog_id = Library_Share::getRequest('blog_id');
		$db = new Data_Blog();
		$blog = $db->getBlogInfo($blog_id);

		$view = new Vera_View(true);
		$view->assign('user', $_SESSION['yb_user_info']['yb_userid']);
		$view->assign('title', $blog['name']);
		$view->assign('blog_id', $blog['id']);
		$view->display('meet/ActivityLaunch.tpl');
		return true;
	}

	private function getActivityMember(){
		$activity_id = Library_Share::getRequest('activity_id', Library_Share::INT_DATA);
		if(!is_bool($activity_id) && !$activity_id){
			return false;
		}

		$data = Service_Blog::getActivityMemberList($activity_id);
		// echo '<br/><br/> ActivityMemberList <br/>';
		// var_dump($data);

		$view = new Vera_View(true);//开启debug模式
		$view->assign('activity_id', $activity_id);
		$view->assign('title', $data['title']);
		$view->assign('blog_id', $data['blog_id']);
		$view->assign('list',$data['list']);
		$view->assign('user',$_SESSION['yb_user_info']['yb_userid']);
		$view->display('meet/ActivityMember.tpl');
		return true;
	}

	private function test(){
		$db = Vera_Database::getInstance();
		//1.获取部落列表
		$blogList = $db->select('meet_Blog', 'id');

		//2.获取各个部落的成员列表及其总人数
		foreach($blogList as $key => $value){
			$blogList[$key]['point'] = 0;
			//2.1获取各个部落的成员人数(包括部落创建人)
			$blogList[$key]['memberCount'] = $db->selectCount('meet_BlogMember', array('blog_id'=>$value['id']));
			$blogList[$key]['point'] += $blogList[$key]['memberCount'];
			//2.3获取各个部落的活动列表
			$blogList[$key]['activityList'] = $db->select('meet_Activity', 'id activity_id', array('blog_id'=>$value['id']));
			//2.4获取各个部落的活动数量
			$blogList[$key]['activityCount'] = count($blogList[$key]['activityList']);
			$blogList[$key]['point'] += $blogList[$key]['activityCount'];
			//2.5获取各个活动的相关信息
			foreach($blogList[$key]['activityList'] as $index => $worth){
				//2.5.1活动的参与人员数量(包括活动发起人)
				$blogList[$key]['activityList'][$index]['memberListCount'] = $db->selectCount('meet_ActivityMember', array('activity_id' => $worth['activity_id']));
				$blogList[$key]['point'] += $blogList[$key]['activityList'][$index]['memberCount'];
			}

			//2.6更新部落信息
			$data = array(
				'point' => $blogList[$key]['point']);
			$conds = array(
				'id' => $blogList[$key]['id'] );
			$db->update('meet_Blog', $data, $conds);
		}

		$userList = $db->select('meet_BlogMember', '*');

		//更新用户在部落内的积分
		foreach($userList as $key => $value){
			//.1获取用户参加的部落列表
			//$userList[$key]['blogList']
		}

		//更新用户的interpersonal值
		$user = array();
		foreach($userList as $key => $value){
			$user[$value['user_ybid']] = 0;
		}
		foreach($userList as $key => $value){
			$user[$value['user_ybid']] += $value['point'] + 1;
		}
		foreach($user as $key => $value){
			$conds = array('ybid' => $key);
			$data = array('interpersonal' => $value);
			$db->update('meet_User', $data, $conds);
		}

		$view = new Vera_View(true);
		$view->display('meet/Blog.tpl');
		return true;
	}
}
?>