<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Text.php
*    description:     文本关键词Api
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
* 推送面板 api
*/
class Action_Api_Text extends Action_Base
{
    function __construct() {}

    public function run()
    {
        if (!isset($_GET['m'])) {
            return false;
        }

        switch ($_GET['m']) {

            case 'add':
                if (!isset($_POST['word']) || !isset($_POST['content'])) {
                    return false;
                }
                return $this->_add($_POST['word'], $_POST['content']);
                break;
            case 'update':
                if (!isset($_POST['id']) || !isset($_POST['word']) || !isset($_POST['content'])) {
                    return false;
                }
                return $this->_update($_POST['id'], $_POST['word'], $_POST['content']);
                break;

            default:
                return false;
                break;
        }
    }

    private function _add($word, $content)
    {
        $ret = array('errno'=>'0','errmsg'=>'OK');
        $content = json_decode($content,true);
        $data = new Data_Wechat();
        $data->setKeyword($word,$content['type'],$content['data']);

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private function _update($id, $word, $content)
    {
        $ret = array('errno'=>'0','errmsg'=>'OK');
        $content = json_decode($content,true);
        $data = new Data_Wechat();
        $data->setKeyword($word,$content['type'],$content['data'], $id);

        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

}

?>
