<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Checkin.php
*    description:     签到平台Api
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  签到平台Api
*/
class Action_Api_Checkin extends Action_Base
{

    function __construct()
    {

    }

    public function run()
    {
        if (!isset($_GET['m'])) {
            return false;
        }

        switch ($_GET['m']) {

            case 'get':
                if (!isset($_GET['date'])) {
                    return false;
                }
                return $this->_get($_GET['date']);
                break;

            case 'update':
                if (!isset($_POST['time']) || !isset($_POST['data'])) {
                    return false;
                }
                return 
                    $this->_update($_POST['time'],$_POST['data']);
                break;


            default:
                return false;
                break;
        }
    }

    private function _get($date)
    {
        $ret = array('errno'=>'0','errmsg'=>'OK');
        Vera_Autoload::changeApp('checkin');
        $service = new Service_Question();
        $question = $service->getQuestion($date);
        Vera_Autoload::reverseApp();
        if ($question) {
            $ret = array(
                'errno'    => '0',
                'errmsg'   => 'OK',
                'data'     => array(
                    'id'       => $question['id'],
                    'content'  => $question['content'],
                    'option'   => $question['option'],
                    'optionType'     => $question['optionType'],
                    'remark' => $question['remark'],
                    'questionType' => $question['questionType'],
                    'rightAnswer' => explode('|', $question['rightAnswer']),
                    'count' => $question['count']
                    )
                );
        }
        else {
            $ret = array(
                'errno'    => '0',
                'errmsg'   => 'OK',
                'data'     => array()
                );
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _update($time, $data)
    {
        $ret = array('errno'=>'0','errmsg'=>'OK');

        $data = json_decode($data,true);
        $temp = array();
        foreach ($data['option'] as $each) {
            $temp[]= $each['key'].'.'.$each['value'];
        }
        unset($data['option']);
        $data['questionOption'] = implode('|',$temp);

        if($data['questionType'] == 0){
            if($data['optionType'] == 'radio'){
                $rightAnswer = $data['rightAnswer'];
            }else{
                $rightAnswer = implode('|', $data['rightAnswer']);
            }
            unset($data['rightAnswer']);
            $data['rightAnswer'] = $rightAnswer;
        }else{
            $data['rightAnswer'] = null;
        }


        Vera_Autoload::changeApp('checkin');
        $service = new Data_Db();
        $ret['data'] = $service->insertQuestion($time, $data);
        Vera_Autoload::reverseApp();

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }
}

?>
