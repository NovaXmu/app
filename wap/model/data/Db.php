<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Db.php
*    description:     数据库交互 Data 类
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  数据库交互
*/
class  Data_Db extends Data_Base
{

    function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * 获取当前用户的绑定情况
     * @return  array  绑定情况
     */
    public function getLinkinInfo()
    {
        $ret = array('xmu'=>'0','yiban'=>'0');
        if ($this->isLink()) {
            $ret['xmu'] = '1';
        }
        if ($this->isYibanLink()) {
            $ret['yiban'] = '1';
        }
        return $ret;
    }

    /**
     * 更新数据库中厦大绑定信息
     * @param   string $num       学号
     * @param   string $password  密码
     * @return  int     影响行数
     */
    public function updateXmu($num, $password)
    {
        $db = Vera_Database::getInstance();
        $user = $db->select('User', '*', array('xmuId' => $num));
        $resource = $this->getResource();
        if(empty($user)){//没有xmu的记录
            $wechat = $db->select('User', '*', array('wechatOpenid' => $resource['openid']));
            $rows = array(
                'xmuId' => $num,
                'xmuPassword' => $password,
                'isLinkedXmu' => 1,
                'linkXmuTime' => date("Y-m-d H:i:s")
                );
            if(!empty($wechat)){
               return $db->update('User', $rows, array('wechatOpenid' => $resource['openid']));
            }else{
                $rows['wechatOpenid'] = $resource['openid'];
                return $db->insert('User', $rows);
            }
        }else{//有xmu记录
            $wechat = $db->select('User', '*', array('wechatOpenid' => $resource['openid']));
            if(!empty($wechat)){//有wechat记录
                if($wechat[0]['xmuId'] == $num){//WeChat记录和xmu记录相同
                    $rows = array(
                        'xmuPassword' => $password,
                        'isLinkedXmu' => 1,
                        'linkXmuTime' => date('Y-m-d H:i:s')
                        );
                    return $db->update('User', $rows, array('id' => $user[0]['id']));
                }else{//WeChat记录和xmu记录不同
                    $db->update('User', array('wechatOpenid' => null), array('id' => $wechat[0]['id']));
                    $rows = array(
                        'xmuPassword' => $password,
                        'isLinkedXmu' => 1,
                        'linkXmuTime' => date('Y-m-d H:i:s'),
                        'wechatOpenid' => $resource['openid']
                        );
                    return $db->update('User', $rows, array('id' => $user[0]['id']));
                }
            }else{//没有WeChat记录
                $rows = array(
                    'xmuPassword' => $password,
                    'isLinkedXmu' => 1,
                    'linkXmuTime' => date('Y-m-d H:i:s'),
                    'wechatOpenid' => $resource['openid']
                    );
                return $db->update('User', $rows, array('id' => $user[0]['id']));
            }
        }
    }

    /**
     * 更新vera_Yiban表中易班绑定信息
     * @param string $xmu_num 学号
     * @param   string $userid       易班id
     * @param   string $access_token       易班token
     * @param   string $expires     过期时间
     * @return  int 影响行数
     * @author  Nili
     */
    public static function linkYiban($xmu_num,$userid, $access_token, $expires)
    {
        $db = Vera_Database::getInstance();
        $update = array(
                'accessToken' => $access_token,
                'expireTime' => $expires
            );
        $insert = $update;
        $insert['uid'] = $userid;
        return $db->insert('Yiban', $insert, null, $update);
        // $user = $db->select('User', '*', array('xmuId' => $xmu_num));
        // if(!empty($user)){
        //     $yiban = $db->select('User', '*', array('yibanUid' => $userid));
        //     if(!empty($yiban)){
        //         if($yiban[0]['id'] == $user[0]['id']){
        //            $rows = array('yibanUid' => $userid,
        //             'isLinkedYiban' => 1,
        //             'linkYibanTime' => date('Y-m-d H:i:s')
        //             ); 
        //            return $db->update('User', $rows, array('id' => $user[0]['id']));
        //         }else{
        //             // if(empty($yiban[0]['wechatOpenid']) || empty($yiban[0]['xmuId']))
        //             //     $db->delete('User', array('id' => $yiban[0]['id']));
        //             // else
        //             //     $db->update('User', array('yibanUid' => null, 'isLinkedYiban' => -1), array('id' => $yiban[0]['id']));
        //             // 
        //             $db->update('User', array('yibanUid' => null, 'isLinkedYiban' => -1), array('id' => $yiban[0]['id']));
        //             $rows = array('yibanUid' => $userid,
        //             'isLinkedYiban' => 1,
        //             'linkYibanTime' => date('Y-m-d H:i:s')
        //             );
        //             return $db->update('User', $rows, array('id' => $user[0]['id']));
        //         }
        //     }else{
        //         $rows = array(
        //                 'xmuId' => $xmu_num,
        //                 'yibanUid' => $userid,
        //                 'isLinkedYiban' => 1,
        //                 'linkYibanTime' => date('Y-m-d H:i:s')
        //                 );
        //         return $db->insert('User', $rows);
        //     }
        // }
    }

    /**
     * 更新vera_User表中易班绑定信息
     * @param   string $openid  微信openid
     * @param   int     yiban_uid    用户易班id
     * @return  int 影响行数
     * @author  Nili
     */
    public static function updateYiban($openid, $yiban_uid)
    {
        $update = array('yibanUid' => $yiban_uid, 'linkYibanTime' => date('Y-m-d H:i:s'), 'isLinkedYiban' => 1);
        $insert = $update;
        $insert['wechatOpenid'] = $openid;
        $db = Vera_Database::getInstance();
        $wechat = $db->select('User', '*', array('wechatOpenid' => $openid));
        $yiban = $db->select('User', '*', array('yibanUid' => $yiban_uid));
        if(!empty($wechat)){
            if(!empty($yiban)){
                if($yiban[0]['id'] == $wechat[0]['id']){
                    return $db->update('User', array('isLinkedYiban' => 1, 'linkYibanTime' => date('Y-m-d H:i:s')), array('id' => $wechat[0]['id']));
                }else{
                    // if(empty($yiban[0]['wechatOpenid']) || empty($yiban[0]['xmuId']))
                    //     $db->delete('User', array('id' => $yiban[0]['id']));
                    // else
                    $db->update('User', array('yibanUid' => null, 'isLinkedYiban' => -1), array('id' => $yiban[0]['id']));
                    return $db->update('User', array('yibanUid' => $yiban_uid, 'isLinkedYiban' => 1, 'linkYibanTime' => date('Y-m-d H:i:s')), array('id' => $wechat[0]['id']));
                }
            }else{
                return $db->update('User', array('yibanUid' => $yiban_uid, 'isLinkedYiban' => 1, 'linkYibanTime' => date('Y-m-d H:i:s')), array('id' => $wechat[0]['id']));
            }
        }else{
            if(!empty($yiban)) {
                $db->delete('User', array('id' => $yiban[0]['id']));
            }
            return $db->insert('User', array('yibanUid' => $yiban_uid, 'isLinkedYiban' => 1, 'linkYibanTime' => date('Y-m-d H:i:s'), 'wechatOpenid' => $openid));

        }
    }

    /**
     * vera_User表里解绑易班
     * @param   string $openid       微信openid
     * @return  bool
     * @author  Nili
     */
    public static function unLinkYiban($openid)
    {
        $db = Vera_Database::getInstance();
        $db->update('User', array('isLinkedYiban' => 0), array('wechatOpenid' => $openid));
        return true;
    }

    /**
     * 更新数据库中易班绑定信息
     * @param   string $num       学号
     * @param   string $password  密码
     * @return  bool
     */
    public function unLinkXmu()
    {
        if ($this->isLink()) {//已绑定过
            $db = Vera_Database::getInstance();
            $id = $this->getID();
            $set = array(
                'isLinkedXmu' => 0
            );
            $db->update('User', $set, array('id' => $id));
        }
        return true;
    }


    /**
    *   登录验证获取登录cookie
    *
    *   @param int 学号
    *   @param string 密码
    *   @return bool 成功时返回true，失败时false
    */
    public function xmuCheck($num, $password)
    {
        $post_data = "Login.Token1=".$num;
        $post_data.= "&Login.Token2=".$password;

        $handle = curl_init();

        $options = array(
                    CURLOPT_URL            => 'http://idstar.xmu.edu.cn/amserver/UI/Login',
                    CURLOPT_HEADER         => 0,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_COOKIEJAR      => "",
                    CURLOPT_POST           => 1,
                    CURLOPT_POSTFIELDS     => $post_data
                    );
        curl_setopt_array($handle, $options);

        curl_exec($handle);//执行

        if(curl_getinfo($handle,CURLINFO_HTTP_CODE) == 302)//302说明验证成功
            return true;

        return false;
    }

    /**
     * @return array 所有老师所有信息
     * @author nili <nl_1994@foxmail.com>
     */
    public static function getAllTeachers()
    {
        $db = Vera_Database::getInstance();
        return $db->select('wap_Teacher', '*');
    }

    /**
     * @param string $yb_uid 易班id
     * @return array 某人所有投票记录
     * @author nili <nl_1994@foxmail.com>
     */
    public static function getVoteLog($yb_uid)
    {
        $db = Vera_Database::getInstance();
        return $db->select('wap_TeacherLog', '*', "yb_uid=$yb_uid");
    }

    /**
     * @param string $yb_uid 易班id
     * @param int $teacher_id 老师id
     * @return int 影响行数
     * @author nili <nl_1994@foxmail.com>
     */
    public static function vote($teacher_id, $yb_uid)
    {
        $db = Vera_Database::getInstance();
        $db->insert('wap_TeacherLog', array(
            'yb_uid' => $yb_uid,
            'teacher_id' => $teacher_id,
            'time' => date('Y-m-d H:i:s')));
        $where = array('id' => $teacher_id);
        $set = 'vote=vote + 1';
        $db->update('wap_Teacher', $set, $where);
    }
}

?>
