<?php
/**
*	@copyright
*
* 	file:       Error.php
*	description: 错误内容处理函数
*
*	@author linjun
*/

class Action_Error extends Action_Base{

	function __construct($resource){
		parent::__construct($resource);
	}

	public static function run($exception){
		$log = 'file[' . $exception->getFile() . ']';
		$log = 'line[' . $exception->getLine() . ']';
		$log = 'message[' . $exception->getMessage() . ']';
		Vera_Log::addErr($log);
		return true;
	}
}
?>