<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Info.php
*    description:    抢票平台Service层，抢票活动信息
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*
*/
class Service_Info
{
    private static $resource = NULL;

    function __construct($_resource = NULL)
    {
        self::$resource = $_resource;
    }

    /**
     * 获取活动详情
     * @param  int $actID 活动id
     * @return array        活动信息
     */
    public function getInfo()
    {
        $actID = self::$resource['actID'];
        $data = new Data_Db(self::$resource);

        //检查活动合法
        $info = $data->getAct($actID);
        if (!$info) {
            $ret['errno'] = 4201;
            $ret['errmsg'] = '活动不存在';
            return $ret;
        }

        return $info;
    }

    public function getUserTicket()
    {
        $actID = self::$resource['actID'];
        $data = new Data_Db(self::$resource);
        $result = $data->getUserInAct($actID);
        if ($result['result'] == 1) {
            $ret = array(
                    'result' => $result['result'],
                    'token' => $result['accessToken']
                );
        }
        else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * 余票数量
     * @return  int  数量
     */
    public function getLeftTicket()
    {
        $actID = self::$resource['actID'];
        $data = new Data_Db(self::$resource);
        $actInfo = $data->getAct($actID);
        return $data->isLeft($actInfo);
    }

    /**
     * 剩余抢票次数
     * @return  int 剩余次数
     */
    public function getLeftChance()
    {
        $actID = self::$resource['actID'];
        $data = new Data_Db(self::$resource);
        $actInfo = $data->getAct($actID);
        $count = $data->countOfUser($actID);
        return $actInfo['times'] - $count;
    }

    /**
     * 抢票贴列表
     * @return  array  列表信息
     */
    public function getList()
    {
        $data = new Data_Db(self::$resource);
        $list = $data->getList();
        if (empty($list)) {
            throw new Exception("暂时无抢票活动", 4204);
        }

        $ret = array();
        $i = 0;
        foreach ($list as $each) {
            $ret[$i]['actID'] = $each['actID'];
            $ret[$i]['name'] = $each['name'];
            $ret[$i]['total'] = $each['total'];
            $ret[$i]['times'] = $each['times'];
            $ret[$i]['startTime'] = $each['startTime'];
            $ret[$i]['endTime'] = $each['endTime'];
            $ret[$i]['link'] = $this->_getLink($each['actID']);
            $i++;
        }
        return $ret;
    }

    /**
     * 获取抢票链接(只能展示使用，因缺少openid而无法抢票)
     * @param  int $actID 活动id
     * @return string       抢票url
     */
    private static function _getLink($actID, $openid = '')
    {
        $conf = Vera_Conf::getAppConf('common');
        $url = $conf['ticketUrl'] . "?actID={$actID}&openid={$openid}";
        return $url;
    }

}
?>
