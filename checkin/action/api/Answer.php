<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Answer.php
*    description:    签到平台回答问题Api
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
* 回答问题Api
*/
class Action_Api_Answer extends Action_Base
{

    function __construct() {}

    public function run()
    {
        $question_id = $_GET['id'];
        $answer = $_GET['answer'];
        $source = isset($_GET['source']) ? $_GET['source'] : 'unknown';

        $resource = $this->getResource();
        $model = new Data_Db($resource);
        $service = new Service_Answer($resource);

        //检查是否已绑定
        if (!$model->isLinked()) {
            $ret = array('errno' => '6002', 'errmsg' => '未绑定厦大帐号');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        //检查是否已签到
        if ($model->isChecked()) {
            $ret = array('errno' => '6003', 'errmsg' => '今日已签到');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }
        //检查是否是当天的问题
        $question = $model->getQuestion();
        if ($question['id'] != $question_id) {
            $ret = array('errno' => '6004', 'errmsg' => '非当日问题');
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return false;
        }

        //签到
        $model->addCheckinLog($question_id, $answer, $source);

        //获取签到结果
        $info = $service->checkinInfo($question_id, $answer);

        //返回json数组
        $ret = array(
            'errno'      => '0',
            'errmsg'     => 'OK',
            'data'       => $info
            );
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}

?>
