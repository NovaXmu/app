<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Question.php
*    description:    签到平台请求问题内容API
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
* 问题内容API
*/
class Action_Api_Question extends Action_Base
{

    function __construct() {}

    public function run()
    {
        $resource = $this->getResource();
        $model = new Service_Question($resource);

        //获取问题
        $question = $model->getQuestion();
        $ret = array(
            'errno'    => '0',
            'errmsg'   => 'OK',
            'data'     => array(
                'id'       => $question['id'],
                'content'  => $question['content'],
                'option'   => $question['option'],
                'optionType'     => $question['optiontype'],
                'questionType'  => $question['questionType']
                )
            );
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}

?>
