<?php
/**
*
*   @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*   All rights reserved
*
*   file:           Func.php
*   description:    抢票平台功能实现类
*
*   @author Yuri
*   @license Apache v2 License
*
**/

/**
* 抢票平台处理类
*/
class Data_Func extends Data_Base
{

    function __construct() {}

    /**
     * 随机抢票方式
     * @return  bool  是否抢到了票
     */
    public function random($chance)
    {
        $ret = array();
        $ret['result'] = $this->_randomResult($chance);

        if($ret['result'] == 1) {
            $token = $this->_getToken();
            $ret['token'] = $token;
        }
        else {
            $ret['token'] = '';
        }

        return $ret;
    }

    /**
     * 随机抽奖函数
     * @param  int $chanceArr  中奖概率
     * @return  bool             中奖与否
     */
    private function _randomResult($chance)
    {
        $chance = floor($chance);
        $rand = mt_rand(1, 100);
        if ($rand <= $chance)
            return 1;
        else
            return 0;
    } 

    /**
     * 生成TOKEN
     * @return string token
     */
    private function _getToken()
    {
        $token = "";
        for ($i=0; $i < 12; $i++) {
            $token.= mt_rand(0,9);
        }
        return $token;
    }
}

?>
