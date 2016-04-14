<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            Func.php
*    description:     会场签到Service层
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**
* 功能封装
*/
class Service_Func
{
    function __construct() {}

    /**
     * 生成新的签到token
     * @param  string $act 活动token
     * @return string      二维码token
     */
    public static function newTokenFor($act)
    {
        $refresh = 15;
        $time = $refresh - time() % $refresh;
        sleep($time);//每$refresh秒更新一次token
        // act&token
        return $act . '&' . Data_Db::newToken($act);
    }

    /**
     * 执行签到
     * @param  string $act   活动token
     * @param  string $token 二维码token
     * @param  int $num      学号
     * @return array         签到活动的信息和签到排名
     */
    public static function checkin($act, $token, $num)
    {
        if (!Data_Db::isTokenValid($act, $token)) {
            $ret = array('errno' => 1, 'errmsg' => '手慢了，赶紧再扫一下吧');
            return $ret;
        }

        $actInfo = Data_Db::getActInfo($act);
        if ($msg = self::checkLimitConds($num, $actInfo['limitConds']))
        {
            $ret = array('errno' => 1, 'errmsg' => $msg);
            return $ret;
        }

        if (!$rank = Data_Func::checkin($act, $num)) {
            $ret = array('errno' => 1, 'errmsg' => '签到失败');
            return $ret;
        }
        
        $awardMsg = '';
        if ($actInfo['award'] > 0) 
        {
            $awardMsg = self::pay($num, $actInfo['award'], $act, $actInfo['end']);
        }
        $ret = array(
            'errno' => 0,
            'actInfo' => $actInfo,
            'rank' => $rank,
            'awardMsg' => $awardMsg
        );
        return $ret;
    }

    /**
     * 扫码送网薪接口
     * @param string $num 厦大学号
     * @param int $award 发放网薪额度
     * @param string $act 扫码活动md5
     * @param string $end 扫码活动结束时间
     * @return string 网薪发放情况
     * @author Nili 
     */
    public static function pay($num, $award, $act, $end) 
    {
        $yibanInfo = Data_Db::getYibanInfoByXmuNum($num);
        $key = 'rollcall_' . $act . '_award' . $num;
        $cache = Vera_Cache::getInstance();
        if (!empty($cache->get($key)))
        {
            return '您已在该活动获得过网薪';
        }
        if (!$yibanInfo['yiban_islinked'])
        {
            return '未绑定易班身份，无法获得网薪';
        }
        if ($yibanInfo['expire_time'] < date('Y-m-d H:i:s'))
        {
            return '易班身份已过期，无法获得网薪';
        }

        Vera_Autoload::changeApp('yiban');
        $ret = Data_Yiban::awardSalary($yibanInfo['yiban_uid'], $yibanInfo['access_token'], $award);
        Vera_Autoload::reverseApp();
        if ($ret)
        {
            $cache->set($key, 1, $end);
            return "{$award}网薪已发往您的账户";
        }
        else
        {
            return '';//网薪发放失败
        }
    }

    /**
     * 扫码限制条件检测
     * @param array $limitConds 限制条件
     * @param string  $xmu_num 厦大学号
     * @return  string 为空则满足限制条件，否则则返回某限制条件不满足的描述
     * @author nili
     */
    
    public function checkLimitConds($xmu_num, $limitConds = array())
    {
        if (empty($limitConds))
        {
            return '';
        }
        if (!isset($limitConds['grade']) || empty($limitConds['grade']))
        {
            return '';//临时校验，之后再完善
        }
        $symbol = $limitConds['grade'][0];
        $value = substr($limitConds['grade'], 1);
        $grade = substr($xmu_num, 3,4);
        switch ($symbol) {
            case '=':
                if ($grade == $value)
                    return '';
                return "本次活动仅面向{$value}级学生";
            case '!':
                if ($grade != $value)
                    return '';
                return "本次活动仅面向非{$value}级学生";
            case '>':
                if ($grade > $value)
                    return '';
                return "本次活动仅面向{$value}级以下学生";
            case '<':
                if ($grade < $value)
                    return '';
                return "本次活动仅面向{$value}级以上学生";
            default:
                return '条件非法';
                break;
        }
    }
}
?>
