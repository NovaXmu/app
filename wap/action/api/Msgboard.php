<?php
/**
*
*   @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*   All rights reserved
*
*   file:             Msgboard.php
*   description:      移动端留言板api
*
*   @author Yuri <zhang1437@gmail.com>
*   @license Apache v2 License
*
**/

class  Action_Api_Msgboard extends Action_Base
{

    function __construct() {}

    public function run()
    {
        try {
            if(!isset($_GET['m'])){
                throw new Exception("need more args", 1);
            }

            switch ($_GET['m']) {
                case 'get':
                    if (!isset($_GET['act']) || empty($_GET['act'])) {
                        throw new Exception("need more args", 1);
                    }
                    self::_get($_GET['act']);
                    break;

                case 'add':
                    if (!isset($_GET['act']) || empty($_GET['act']) || !isset($_GET['content']) || empty($_GET['content'])) {
                        throw new Exception("need more args", 1);
                    }
                    self::_add($_GET['act'], $_GET['content']);
                    break;

                default:
                    throw new Exception("unknown mode", 1);
                    break;
            }
        } catch (Exception $e) {
            $ret = array(
                'errno'=>$e->getCode(),
                'errmsg'=>$e->getMessage()
            );
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            return true;
        }
    }

    private static function _get($md5)
    {
        $log = Vera_Log::readLog('board_'.$md5, 100);
        if (!$log) {
            $log = array();
        }
        $ret = array(
            'errno'=>0,
            'data'=> $log
        );
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        return true;
    }

    private static function _add($md5, $content)
    {
        Vera_Log::addLog('board_'.$md5, $content);
        throw new Exception("OK", 0);
    }

}

?>
