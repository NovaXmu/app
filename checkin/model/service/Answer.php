<?php
/**
*
*   @copyright  Copyright (c) 2015 echo Lin
*   All rights reserved
*
*   file:             Answer.php
*   description:      签到平台Answer Service层
*
*   @author Linjun
*   @license Apache v2 License
*
**/

class Service_Answer{
	private static $resource = NULL;

    function __construct($_resource = NULL)
    {
        self::$resource = $_resource;
    }

    public function checkinInfo($question_id, $answer){
    	$db = new Data_Db(self::$resource);

    	$userCheckinInfo = $db->userCheckinInfo();
    	$info['order'] = $userCheckinInfo['order'];
    	$info['monthCount'] = $userCheckinInfo['count'];
        $info['pay'] = $userCheckinInfo['pay'];

    	$info['questionType'] = $db->getQuestionType($question_id);
        //var_dump($info['questionType']);

    	if($info['questionType'] == 0){
    		$info['isRight'] = $db->isRight($question_id, $answer);
            $info['rightAnswer'] = $db->getRightAnswer($question_id);
        }else{
    		$info['optionRate'] = $db->getOptionRate($question_id);
        }

    	$info['remark'] = $db->getRemark($question_id);

    	return $info;
    }
}
?>