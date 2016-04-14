<?php
/**
*	@copyright
*
*	file: Vote.php
*	description: 开放抢票API
*
* 	@author linjun
*
*/
class Action_Api_Public_Ticket{
	function __construct(){}

	public static function run(){
		if(!isset($_GET['m'])){
			return false;
		}

		case 'submit':
			#code...
		case 'list':
		
	}
}
?>