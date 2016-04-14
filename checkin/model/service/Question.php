<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Question.php
*    description:    签到平台Question Service层
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*
*/
class Service_Question
{
    private static $resource = NULL;

    function __construct($_resource = NULL)
    {
        self::$resource = $_resource;
    }

    public function getQuestion($date = NULL)
    {
        $model = new Data_Db(self::$resource);
        $question = $model->getQuestion($date);
        if (!$question) {
            return false;
        }

        $option = explode('|', $question['questionOption']);//处理option
        unset($question['questionOption']);
        $count = count($option);
        for ($i=0; $i < $count; $i++) {
            $temp = explode('.', $option[$i]);
            $option[$i] = array();
            $option[$i]['key'] = $temp[0];
            $option[$i]['value'] = $temp[1];
            $option[$i]['count'] = $model->getOptionCount($question['id'],$temp[0]);
        }
        $question['option'] = $option;

        $question['count'] = $model->getCount($date);

        return $question;
    }

    public function isChecked()
    {
        $model = new Data_Db(self::$resource);
        $result = $model->isChecked();
        if($result) {
            $ret = $model->userCheckinInfo();
            return $ret;
        }
        return false;
    }

}

?>
