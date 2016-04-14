<?php
/**
 *
 *	@copyright  Copyright (c) 2016 Xuxinang
 *	All rights reserved
 *
 *	file:			Db.php
 *	description:	数据库相关操作
 *
 *	@author Xuxinang
 *	@license Apache v2 License
 *	
 **/

class Data_Db 
{
	/**
     * 获取表中指定信息
     * @param  int $processed 处理标识
     *             0:未处理/1:已处理
     * @param  int $messagetype 留言类型 
     *             0:cooperation/1:bugfrontend/2:bugbackend/3:bugother/4:suggestion
     * @param  int $page 页数 
     * @return array 全部信息
     * @author Xuxinang
     */
    public function getMessageData($processed, $messagetype, $page) {
        switch ($messagetype) {
            case 1:
            case 2:
            case 3:
                $field = 'messageid,username,contactway,mailbox,messagecontent,attention,';
                break;
            
            case 0:
            case 4:
                $field = 'messageid,username,contactway,mailbox,messagecontent,';
                break;
        }

        switch ($processed) {
            case 0:
                $field .= 'updatetime';
                break;
            
            case 1:
                $field .= 'creationtime,updatetime';
                break;
        }

        $db = Vera_Database::getInstance();
        $where = array('messagetype' => $messagetype, 'processed' => $processed);
        $appends = 'ORDER BY updatetime DESC LIMIT '.($page * 10).',10';
        $res = $db->select('message_Message', $field, $where, NULL, $appends);
        return $res;
    }

	/**
	 * 管理员确认消息已处理
	 * @param  string $messageid 留言标识
	 * @return string $res 处理结果
	 * @author Xuxinang
	 */
	public function updateComplete($messageid) {
		$db = Vera_Database::getInstance();
		if(self::_isMessageid($messageid) == true) {
			$field = 'processed';
		    $where = array('messageid' => $messageid);
		    $res = $db->select('message_Message', $field, $where);
		    if ($res[0]['processed'] == 0) {
		    	$field = array('processed' => 1);
		        $where = array('messageid' => $messageid);
		        $res = $db->update('message_Message', $field, $where);
		    } else {
		    	$res = '不能重复确认';		    
            }		
		}
		return empty($res) ? 'false' : (is_numeric($res) ? 'true' : $res);
	}

	/**
	 * 向用户推送留言
	 * @return array BUG留言
	 * @author Xuxinang
	 */
	public function getBugMessage() {
		$db = Vera_Database::getInstance();
		$type = array('bugfrontend', 'bugbackend', 'bugother');
		$res = array(
			"$type[0]" => array(),
			"$type[1]" => array(),
			"$type[2]" => array()
		);
		$field = 'messageid,messagecontent,attention,updatetime';
		for ($i = 0; $i < 3; $i++) {
			$where = array('messagetype' => $i + 1, 'processed' => 0);
			$appends = 'ORDER BY attention DESC LIMIT 0,10';
		    $res["$type[$i]"] = $db->select('message_Message', $field, $where, NULL, $appends);
		}
		return $res;
	}

	/**
	 * 用户增添留言
	 * @param  string $username 用户姓名
	 * @param  string $contactway 用户联系方式
	 * @param  string $mailbox 用户邮箱
	 * @param  string $messagetype 留言类型
	 * @param  string $messagecontent 留言内容
	 * @return string 'true'|'false' 成功
	 * @author Xuxinang
	 */
	public function insertMessage($username, $contactway, $mailbox, $messagetype, $messagecontent) {
		$db = Vera_Database::getInstance();
		$insert = array(
			'username' => $username,
			'contactway' => $contactway,
			'mailbox' => $mailbox,
			'messagetype' => $messagetype,
			'messagecontent' => $messagecontent
		);
		$res = $db->insert('message_Message', $insert);
		return empty($res) ? 'false' : 'true';
	}

	/**
	 * 用户赞同留言
	 * @param  string $messageid 留言标识
	 * @return string 'true'|'false' 成功
	 * @author Xuxinang
	 */
	public function updateAttention($messageid) {
		$db = Vera_Database::getInstance();
		if(self::_isMessageid($messageid) == true) {
			$where = array('messageid' => $messageid);
			$field = 'messagetype,attention';
		    $num = $db->select('message_Message', $field, $where);
		    $check = $num[0]['messagetype'];
            if ($check == 1 || $check == 2 || $check == 3) {
            	$update = array('attention' => $num[0]['attention'] + 1);
		        $res = $db->update('message_Message', $update, $where);
            }
		}
		return empty($res) ? 'false' : 'true';
	}

	/**
	 * 确认留言标识存在
	 * @param  string $messageid 留言标识
	 * @return bool true|false 存在
	 * @author Xuxinang
	 */
	private function _isMessageid($messageid) {
		$db = Vera_Database::getInstance();
		$where = array('messageid' => $messageid);
		$res = $db->selectCount('message_Message', $where);
		return empty($res) ? false : true;
	}
}

?>