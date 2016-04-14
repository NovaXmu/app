<?php
/**
*
*	@copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*	All rights reserved
*
*	file:			Db.php
*	description:	抢票平台数据库交互类
*
*	@author Yuri
*	@license Apache v2 License
*
**/

/**
* 抢票平台数据库交互类
*/
class Data_Db extends Data_Base
{
    public $db;

	function __construct($resource = NULL)
	{
		parent::__construct($resource);
        $this->db = Vera_Database::getInstance();
	}

	/**
	 * 检查是否绑定厦大帐号
	 * @return bool
	 */
	public function checkLink()
	{
		return $this->isLink();
	}

	/**
	 * 获取抢票结果
	 * @param  int $actID 活动
	 * @return array       抢票结果
	 */
	public static function getResult($actID)
	{
		$db = Vera_Database::getInstance();
		$condition = array(
			'User.id'      => 'ticket_Log.userID',
			'ticket_Log.actID'  => $actID,
			'ticket_Log.result' => 1
			);
		$ret = $db->select('User,ticket_Log', '*', $condition);
		if (!$ret) {
			return false;
		}
		return $ret;
	}

    /**
     * 获取某人在某个活动的抢中记录
     * @param  [type] $actID [description]
     * @return [type]        [description]
     */
    public function getUserInAct($actID)
    {
        $id = $this->getID();
        $db = Vera_Database::getInstance();
        $condition = array(
            'actID'  => $actID,
            'userID' => $id,
            'result' => 1
            );
        $ret = $db->select('ticket_Log', '*', $condition);
        if (!$ret) {
            return false;
        }
        return $ret[0];
    }

	/**
     * 获取某人票据凭证
     * @param  int $actID 活动id
     * @return string        票据凭证
     */
	public function getToken($actID)
    {
        $id = $this->getID();
        $info = $this->getUserInAct($id, $actID);
        return $info ? $info['accessToken'] : false;
    }

    /**
     * 使用票据
     * @param  int $actID 活动id
     * @param  int $token 票据凭证
     * @return bool        成功或失败
     */
    public function signToken($actID, $token)
    {
        $info = $this->getUserInAct($actID);
        if (!$info || $info['result'] == 0 || $info['isUsed'] == 1 || $token != $info['accessToken']) {
            return false;//已使用过票据或没有token
        }
        $id = $this->getID();
        $db = Vera_Database::getInstance();
        return $db->update('ticket_Log', array('isUsed' => '1'), array('actID' => $actID, 'userID' => $id, 'result' => 1));
    }

    /**
     * 退票
     * @param  int $actInfo 活动信息数组
     * @return bool        退票之后的余票
     */
    public function unSignToken($actInfo, $token)
    {
        $info = $this->getUserInAct($actInfo['actID']);
        if (!$info || $info['result'] == 0 || $info['isUsed'] == 1 || $token != $info['accessToken']) {
            return false;//已使用过票据或没有抽中不可以退票
        }
        $id = $this->getID();
        $db = Vera_Database::getInstance();
        $db->update('ticket_Log', array('result' => '0'), array('actID' => $actInfo['actID'], 'userID' => $id));

        //对缓存做处理
        $cache = Vera_Cache::getInstance();
        $key = 'ticket_'. $actInfo['actID'] .'_left';
        do {
            $left = $cache->get($key, NULL, $cas);//使用Memcached特性cas，保证高并发时票数准确

            if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
                $count = $this->countOfTicket($actInfo['actID']);//重新计算一遍余票
                $left = $actInfo['total'] - $count;
                $cache->add($key, $left, strtotime($actInfo['endTime']));//原子性的插入
            }
            else {
                $cache->cas($cas, $key, $left + 1);//缓存中的余票数+1
            }
        } while ($cache->getResultCode() != Memcached::RES_SUCCESS);

        return $left;
    }

    /**
     * 获取活动信息
     * @return array        活动信息
     */
    public static function getAct($actID, $isPassed = 1)
    {
    	$db = Vera_Database::getInstance();
    	$ret = $db->select('ticket_List', '*', array('actID' => $actID, 'isPassed' => $isPassed));
		if (!$ret) {
			return false;
		}
		return $ret[0];
    }

    /**
     * 获取用户某活动累计抢票次数(缓存)
     * @return int 次数
     */
    public function countOfUser($actID)
    {
        $id = $this->getID();

        $cache = Vera_Cache::getInstance();
        $key = 'ticket_'. $actID .'_'. $id;
        $count = $cache->get($key);
        if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
            $db = Vera_Database::getInstance();
            $count = $db->selectCount('ticket_Log', array('actID' => $actID, 'userID' => $id));
            $cache->set($key, $count);
        }
        return $count;
    }

    public function incrementCountOfUser($actID)
    {
        $id = $this->getID();

        $cache = Vera_Cache::getInstance();
        $key = 'ticket_'. $actID .'_'. $id;
        $cache->get($key);
        if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
            $count = $this->countOfUser($actID);
            $cache->set($key, $count + 1);
        }
        else {
            $cache->increment($key);
        }
    }

    /**
     * 检查有无余票(缓存),
     *
     * @param  int  $actID 活动id
     * @return mixed    返回余票个数
     */
    public function isLeft($actInfo)
    {
        $cache = Vera_Cache::getInstance();
        $left = 0;
        $flag = 0;
        for ($i = 1; $i <= 5; $i ++) {
            $key = 'ticket_'. $actInfo['actID'] .'_left';
            $each = $cache->get($key);
            if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
                $flag = 1;//标志位，标志缓存中余票数据不可信
                $left = 0;
                break;//一旦有一个缓存key不存在，则缓存中余票数据不可信
            }
            $left += $each;
        }
        if ($flag){
            $count = $this->countOfTicket($actInfo['actID']);//读数据库统计发放的票数
            $left = $actInfo['total'] - $count;
            $this->setAllLeftCache($actInfo);
        }
        return $left;
    }


    /**
     * 设置余票缓存，分为五份，其中最后一份保留最多票数
     * @param $actInfo
     */
    public function setAllLeftCache($actInfo)
    {
        $cache = Vera_Cache::getInstance();
        $count = $this->countOfTicket($actInfo['actID']);//读数据库统计发放的票数
        $left = $actInfo['total'] - $count;
        $each = intval($left / 5);
        for ($i = 1; $i <= 4; $i ++) {
            $key = 'ticket_' . $actInfo['actID'] . '_left_' . $i;
            $cache->set($key, $each, strtotime($actInfo['endTime']));//依次插入前四个余票数据
        }
        $last = $left - $each * 4;//不整除的情况,让第五个left中含有最多票
        $key = 'ticket_' . $actInfo['actID'] . '_left_5';
        $cache->set($key, $last, strtotime($actInfo['endTime']));//缓存中插入第五个left，如果不能整除5使其数量最大
    }


    /**
     * 从ticket_$actID_left_$rand开始遍历余票缓存，取出一张票
     * @param $actID
     * @param $rand
     * @return  int     -1表示失败，需重置各缓存，-2表示失败，不需重置缓存，1表示成功
     */
    public function getOneByTraversing($actID, $rand)
    {
        $ret = array();
        for ($i = 0; $i < 4; $i ++) {
            if (($ret[$i] = $this->getOne($actID, ($rand + $i) % 5 + 1)) == 1) {//对5取模再加一，保证getOne的第二个参数取值在1-5之间
                //某一个余票缓存中取票成功
                return 1;
            }
        }
        if (in_array(-1, $ret)) {
            return -1; //取出操作失败，并存在某key不存在的情况，需重置缓存
        }
        return -2;//取出操作失败，但所有缓存key都存在，即所有余票都为0了，不需要重置缓存
    }

    /**
     * 随机从剩余票中取出一张票(缓存)，要求能应对并发
     * @param $actID
     * @param $rand     int     随机数，1-5之间
     * @return int      -1表示缓存不存在，-2表示余票不足，1表示正常
     */
    public function getOne($actID, $rand)
    {
        $cache = Vera_Cache::getInstance();
        $key = 'ticket_'. $actID .'_left_' . $rand;
        do {
            $left = $cache->get($key, NULL, $cas);//使用Memcached特性cas，保证高并发时发放出去的票数准确

            if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
                return -1;//key不存在，可能需要重置缓存以保证票数准确
            }
            else {
                if ($left <= 0)
                    return -2;//余票为0时取出操作失败
                $cache->cas($cas, $key, $left - 1);
            }
        } while ($cache->getResultCode() != Memcached::RES_SUCCESS);

        return 1;
    }

    /**
     * 当前活动已发放的票据总数
     * @param  int $actID 活动的id
     * @return int        票据总数
     */
    public static function countOfTicket($actID)
    {
    	$db = Vera_Database::getInstance();
    	$ret = $db->selectCount('ticket_Log', array('actID' => $actID, 'result' => 1));
    	if (!$ret) {
			return 0;
		}
		return $ret;
    }

	/**
	 * 获取抢票活动列表
	 * @return array 抢票活动列表
	 */
	public static function getList()
	{
		$db = Vera_Database::getInstance();
        $lastDay = date("Y-m-d H:i:s", time() - 86400);//抢票列表可显示已过期一天的抢票活动
		$condition = "endTime >= '{$lastDay}' and isPassed = 1 order by startTime asc";
		$ret = $db->select('ticket_List', '*', $condition);
		if (!$ret) {
			return false;
		}
		return $ret;
	}

	/**
	 * 保存抢票记录
	 * @param  int $actID  活动ID
	 * @param  int $result 抢票结果
	 * @param  string $token  票据
	 * @return int         插入影响的行数
	 */
	public function saveRecord($actID, $result = 0,$token = NULL)
	{
		$db = Vera_Database::getInstance();

		$userID = $this->getID();
		$result = intval($result);
		$token = $result ? $token : NULL;

		$row = array(
				'actID'       => $actID,
				'userID'      => $userID,
				'time'        => date("y-m-d H:i:s"),
				'result'      => $result,
				'accessToken' => $token
			);
		return $db->insert('ticket_Log', $row);
	}
    /**
     * 根据token和actID验证身份
     * @param  int $actID  活动ID
     * @param  int $token   票据凭证
     * @return array ret        查找到的结果
     */

    public function getUserID($actID,$token)
    {
        $db = Vera_Database::getInstance();
        $condition = array(
            'actID'  => $actID,
            'accessToken' => $token,
            'result' => 1
            );
        $ret = $db->select('ticket_Log', '*', $condition);
        //$sql = Vera_Database::getLastSql();
        if (!$ret) {
            return 1;     //没抢中
        }
        if($ret[0]['isUsed'] == 1)
        {
            return 0;     //已兑换
        }
        $ret = $ret[0]['userID'];
        $db->update('ticket_Log', array('isUsed' => '1'), array('actID' => $actID, 'userID' => $ret, 'result' => 1));
        Vera_Log::addNotice('token', $token);
        return $ret;
    }

    public function getStuNumByUserID($userID)
    {
        $db = Vera_Database::getInstance();
        $condition = "`id`=" . $userID;
        $ret = $db->select('User', 'xmuId', $condition);
        //$sql = Vera_Database::getLastSql();
        if (!$ret) {
            return false;
        }
        $ret = $ret[0];
        return $ret['xmuId'];
    }

    public function getUserByUserID($userID)
    {
        $db = Vera_Database::getInstance();
        $condition = "`id`=" . $userID;
        return $db->select('User', '*', $condition);

    }

    /**
     * 参与者从数据库中直接获取抢票活动的列表,列表仅包含活动id、活动名称、开始时间、结束时间
     * @param array $where      条件数组，一般是isPassed或owner作为字段进行筛选
     * @return array|bool|mysqli_result
     */
    function getParticipantTicketList()
    {
        $sql = "SELECT `actID`, `name`, `startTime`, `endTime`, `total`, `times` FROM ticket_List ";
        $now = date('Y-m-d H:i:s');
        $where = "WHERE endTime > '$now' AND isPassed = 1 ";
        $order = "ORDER BY startTime ";
        $sql = $sql . $where . $order;
        return $this->db->query($sql);
    }

    function getTicketLeft($actDetail)
    {
        $count = $this->countOfTicket($actDetail['actID']);//读数据库统计发放的票数
        $left = $actDetail['total'] - $count;
        return $left;
    }

    /**
     * 获取某人在某次活动中的纪录，包括未参与、抢中&未抢中，其中要考虑一个活动可能有多次抢票机会的情形
     * @param $actID
     * @return array    ticket_Log一行+resultStr作为字符串描述
     */
    public function getUserResultInAct($actID, $id)
    {
        $db = Vera_Database::getInstance();
        $condition = array(
            'actID'  => $actID,
            'userID' => $id,
        );
        $res = $db->select('ticket_Log', '*', $condition);
        if (empty($res)) {
            $ret['resultStr'] = '未参与';
            return $ret;
        }
        foreach($res as $row) {
            $ret = $row;
            if ($row['isUsed'] == 1) {
                $ret['resultStr'] = '票据已使用';
            } else if ($row['result'] == 1) {
                $ret['resultStr'] = '已抢中';
            } else if ($row['result'] == 0 && !empty($row['accessToken'])) {
                $ret['resultStr'] = '已退票';
            } else {
                $ret['resultStr'] = '未抢中';
            }
        }
        return $ret;
    }

    function getLog($log_id)
    {
        return $this->db->select('ticket_Log', '*', array('id' => $log_id));
    }

    /**
     * 退票,考虑到退票操作相对较少并且退票失败时提醒错开高峰期来退票不会引起很不友好，
     * 为了保障抢票接口的性能，退票接口将之前的循环至退票成功部分舍去，仅保留一次退票尝试，若失败则提醒用户稍后再试
     * @param $log      array       ticket_Log一列
     * @return boolean
     */
    public function unSignTicket($log)
    {
        $actInfo = $this->getAct($log['actID']);
          //对缓存做处理
        $cache = Vera_Cache::getInstance();
        $key = 'ticket_'. $actInfo['actID'] .'_left';

        $left = $cache->get($key, NULL, $cas);//使用Memcached特性cas，保证高并发时票数准确
        if ($cache->getResultCode() == Memcached::RES_NOTFOUND) {
            $count = $this->countOfTicket($actInfo['actID']);//重新计算一遍余票
            $left = $actInfo['total'] - $count;
            $cache->add($key, $left, strtotime($actInfo['endTime']));//原子性的插入
        } else {
            $cache->cas($cas, $key, $left + 1);//缓存中的余票数+1
        }
        if ($cache->getResultCode() != Memcached::RES_SUCCESS) {
            return false;
        }
        $this->db->update('ticket_Log', array('result' => 0), array('id' => $log['id']));
        return true;
    }

    function getUserHistory($user_id)
    {
        $sql = "SELECT l.*, act.actID, act.name, act.times FROM ticket_Log l
                INNER JOIN ticket_List act ON act.actID = l.actID
                where l.userID = $user_id ORDER BY l.time DESC";
        return $this->db->query($sql);
    }

    function getUser($openID)
    {
        return $this->db->select('User', array('id', 'xmuId','xmuPassword', 'college', 'realName', 'telephone', 'sex', 'grade', 'identity',), array('wechatOpenid' => $openID));
    }

    function getAdminId($openid)
    {
        $admin = $this->db->select('cms_Admin', array('id', 'level'), array('openid' => $openid));
        if (empty($admin)) {
            return 0;
        }
        if ($admin[0]['level'] == 10) {
            return $admin[0]['id']; //10级为开发者，默认权限最高
        }

        $privilege = $this->db->selectCount('cms_Privilege', array('uid' => $admin[0]['id'], 'privilege' => 'Ticket', 'deleted' => 0));
        if (empty($privilege)) {
            return 0;
        }
        return $admin[0]['id'];
    }

    /**
     * 待审核活动列表
     * @return  array  活动数组
     */
    public function getNeedReviewActs()
    {
        $cond = array('isPassed'=>0);
        $append = 'order by startTime asc';
        $result = $this->db->select('ticket_List', '*', $cond, NULL, $append);
        if ($result) {
            foreach ($result as &$each) {
                $each['resultCount'] = $this->_getResultCount($each['actID'],1);
                $each['count'] = $this->_getCount($each['actID']);
            }
            return $result;
        }
        else {
            return array();
        }
    }

    /**
     * 待开始活动列表
     * @return  array  活动数组
     */
    public function getReadyActs()
    {
        $cond = 'isPassed = 1 and startTime > \''.date("Y-m-d H:i:s").'\'';
        $append = 'order by startTime asc';
        $result = $this->db->select('ticket_List', '*', $cond, NULL, $append);
        if ($result) {
            return $result;
        }
        else {
            return array();
        }
    }

    /**
     * 正在进行的活动列表
     * @return  array  活动数组
     */
    public function getOnGoingActs()
    {
        $cond = 'isPassed = 1 and startTime <= now() and now() <= endTime';
        $result = $this->db->select('ticket_List', '*', $cond);
        if ($result) {
            foreach ($result as &$each) {
                $each['resultCount'] = $this->_getResultCount($each['actID'],1);
                $each['count'] = $this->_getCount($each['actID']);
            }
            return $result;
        }
        else {
            return array();
        }
    }

    /**
     * 获取已终止的活动
     * @param   int $isPassed  正常结束的活动或是审核未通过的活动
     * @return  array             活动数组
     */
    public function getEndActs($isPassed = 1)
    {
        $append = NULL;
        if ($isPassed == 1) {
            $cond = 'isPassed = 1 and now() >= endTime';//已正常结束的活动
            $append = 'order by endTime desc limit 0,5';//显示最新的五个
        }
        else {
            $cond = array('isPassed' => -1);//审核未通过的活动
            $append = 'order by actID desc limit 0,5';
        }

        $result = $this->db->select('ticket_List', '*', $cond, NULL, $append);
        if ($result) {
            foreach ($result as &$each) {
                $each['resultCount'] = $this->_getResultCount($each['actID'],1);
                $each['count'] = $this->_getCount($each['actID']);
            }
            return $result;
        }
        else {
            return array();
        }
    }

    /**
     * 设置审批结果
     * @param  int  $actID     活动 id
     * @param integer $isPassed  审批结果，-1未通过，0为审核，1审核通过
     */
    public function setPass($actID, $isPassed = 1)
    {
        $row = array('isPassed'=>$isPassed);
        $cond = array('actID'=>$actID);
        $this->db->update('ticket_List', $row, $cond);

        return true;
    }

    /**
     * 统计参与人数
     * @param   int  $actID   活动 id
     * @param  integer $result  中奖情况
     * @return  int           人数
     */
    private function _getCount($actID)
    {
        $cond = array('actID'=>$actID);
        $ret = $this->db->selectCount('ticket_Log', $cond, 'distinct');
        return $ret;
    }

    /**
     * 统计中奖/未中奖的人数
     * @param   int  $actID   活动 id
     * @param  integer $result  中奖情况
     * @return  int           人数
     */
    private function _getResultCount($actID, $result = 1)
    {
        $cond = array('actID'=>$actID, 'result'=>$result);
        $ret = $this->db->selectCount('ticket_Log', $cond);
        return $ret;
    }

    public function modifyUser($openid, $info)
    {
        return $this->db->update('User', $info, array('wechatOpenid' => $openid));
    }

    /**检查座位是否合法，即同一个位置不能指派给不同用户
     * @param $actID
     * @param $seat
     * @return bool
     */
    public function checkSeatIllegal($actID, $seat)
    {
        if ($this->db->select('ticket_Log', 'id', array('actID' => $actID, 'seat' => $seat))) {
            return false;
        }
        return true;
    }

    public function assignSeat($logId, $seat)
    {
        $this->db->update('ticket_Log', array('seat' => $seat), array('id' => $logId));
    }

    public function getLogByEffectiveToken($actID, $token)
    {
        return $this->db->select('ticket_Log', '*', array('actID' => $actID, 'accessToken' => $token, 'result' => 1));
    }

    public function setTokenUsed($logId)
    {
        $this->db->update('ticket_Log', array('isUsed' => 1), array('id' => $logId));
    }
}

?>
