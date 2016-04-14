<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Blog.php
*   description:      部落api
*
*   @author Linjun
*   @license Apache v2 License
*
**/

class Action_Api_Blog extends Action_Base{

	function __construct(){}

	public function run(){
		$m = Library_Share::getRequest('m');
		if(is_bool($m) && !$m){
			$return = array('errno' => '1', 'errmsg' => '参数不对');
		}else{
			switch($m){
			case 'joinBlog'://test pass
				$return = $this->joinBlog();
				break;
			case 'quitBlog'://test pass
				$return = $this->quitBlog();
				break;
			case 'addBlog'://test pass
				$return = $this->addBlog();
				break;
			}
		}
		
		if(isset($return['errno']) && $return['errno'] == 1){
            $log = Library_Share::getLog(true, $return['errmsg']);
        }else{
            $log = Library_Share::getLog(true);
        }
        $log = json_encode($log, JSON_UNESCAPED_UNICODE);
        Vera_Log::addLog('api', $log);

        echo json_encode($return, JSON_UNESCAPED_UNICODE);
        return true;
	}

/**
 * 加入部落
 *
 * @return boolean 是否成功加入部落
 */
	private function joinBlog(){

		$blog_id = Library_Share::getRequest('blog_id');
		if(is_bool($blog_id) && !$blog_id){
			$ret = array('errno' => '1','errmsg' => '没有选择部落id');
			return $ret;
		}

		$result = Service_Blog::joinBlog($blog_id);

		if(is_int($result)){
			switch($result){
				case 1:
					$ret = array('errno'=> '1', 'errmsg' => '您是该部落的创建者，不可点击加入');
					break;
				case 2:
					$ret = array('errno'=> '1', 'errmsg' => '您已经加入了该部落，不可重复加入');
					break;
				case 3:
					$ret = array('errno'=> '1', 'errmsg' => '加入部落失败,请稍后再试');
					break;
				case 4:
					$ret = array('errno'=> '1', 'errmsg' => '部落积分增加失败');
					break;
				case 5:
					$ret = array('errno'=> '1', 'errmsg' => '您的interpersonal值增加失败');
					break;
			}
			return $ret;
		}

		$ret = array('errno' => '0','errmsg' => 'ok');
		return $ret;
	}

/**
 * 退出部落
 *
 * @return boolean 是否成功退出部落
 */
	private function quitBlog(){

		$blog_id = Library_Share::getRequest('blog_id');
		if(is_bool($blog_id) && !$blog_id){
			$ret = array('errno' => '1','errmsg' => '没有选择部落id');
			return $ret;
		}
		
		$result = Service_Blog::quitBlog($blog_id);
		if(is_int($result)){
			switch($result){
				case 1:
					$ret = array('errno'=> '1', 'errmsg' => '您是部落的创建者，不可以退出部落');
					break;
				case 2:
					$ret = array('errno'=> '1', 'errmsg' => '您没有加入该部落，不可以退出部落');
					break;
				case 3:
					$ret = array('errno'=> '1', 'errmsg' => '退出部落失败');
					break;
				case 4:
					$ret = array('errno'=> '1', 'errmsg' => '部落积分减少失败');
					break;
				case 5:
					$ret = array('errno'=> '1', 'errmsg' => '您的interpersonal值减少失败');
					break;
			}
			return $ret;
		}

		$ret = array('errno' => '0','errmsg' => 'ok');
		return $ret;
	}

/**
 * 申请部落
 *
 * @return  boolean 是否成功申请部落
 */
	private function addBlog(){

		$data = Library_Share::getRequest('data', Library_Share::ARRAY_DATA);

		$result = Service_Blog::addBlog($data);

		if(!is_bool($result)){
			switch($result){
				case 1:
					$ret = array('errno' => '1', 'errmsg' => '没有数据');
					break;
				case 2:
					$ret = array('errno' => '1','errmsg' => '没有输入部落名或简介');
					break;
				case 3:
					$ret = array('errno' => '1','errmsg' => '简介长度必须小于50');
					break;
				case 4:
					$ret = array('errno'=> '1', 'errmsg' => '申请部落失败');
					break;
				case 4:
					$ret = array('errno'=> '1', 'errmsg' => '您的interpersonal值增加失败');
					break;
			}
			return $ret;
		}

		$ret = array('errno' => '0','errmsg' => 'ok');
		return $ret;
	}

}

?>