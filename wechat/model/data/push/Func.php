<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            Func.php
*    description:     推送平台功能封装
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  推送功能封装
*/
class Data_Push_Func extends Data_Base
{
    private $_content;//用于推送的内容
    private $_api = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s';

    function __construct($content) {
        parent::__construct();
        $content['data'] = array_change_key_case($content['data'],CASE_LOWER);//数组键名全小写，以满足推送 api 的要求
        $temp = array(
                    'touser'      =>'',
                    'msgtype'     =>$content['type'],
                    $content['type'] => $content['data']
                );
        $this->_content = $temp;
        $this->_api = sprintf($this->_api, $this->accessToken);
    }

    public function push($openid)
    {
        $postData = $this->_content;
        $postData['touser'] = $openid;
        $postData = json_encode($postData,JSON_UNESCAPED_UNICODE);

        $handle = curl_init();
        $options = array(CURLOPT_URL => $this->_api,
                         CURLOPT_HEADER => 0,
                         CURLOPT_RETURNTRANSFER => 1,
                         CURLOPT_POST => 1,
                         CURLOPT_POSTFIELDS => $postData
                        );
        curl_setopt_array($handle, $options);
        $result = curl_exec($handle);//执行
        $json = json_decode($result,true);
        if ($errno = curl_errno($handle) || $json['errcode'] != 0)//检查是否有误
        {
            Vera_Log::addWarning('push to ['. $openid .'] failed, return '. $result);
            return false;
        }

        return true;
    }

    public function pushList($list)
    {
        $queue = curl_multi_init();
        $map = array();
        $postData = $this->_content;
        foreach ($list as $each) {
            $postData['touser'] = $each;
            $post = json_encode($postData,JSON_UNESCAPED_UNICODE);

            $handle = curl_init();
            $options = array(CURLOPT_URL => $this->_api,
                             CURLOPT_HEADER => 0,
                             CURLOPT_RETURNTRANSFER => 1,
                             CURLOPT_POST => 1,
                             CURLOPT_POSTFIELDS => $post,
                             CURLOPT_NOSIGNAL => true,
                             CURLOPT_TIMEOUT => 100
                            );
            curl_setopt_array($handle, $options);
            curl_multi_add_handle($queue, $handle);
            $map[(string) $handle] = $each;
        }

        $responses = array();
        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;

            if ($code != CURLM_OK) { break; }

            // 检查已完成的请求
            while ($done = curl_multi_info_read($queue)) {
                // 获取该请求的结果
                $error = curl_error($done['handle']);//curl 的 error 信息
                $result = json_decode(curl_multi_getcontent($done['handle']), true);
                $responses[$map[(string) $done['handle']]] = compact('error', 'result');
                // 移除已完成的请求
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }

            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }

        } while ($active);

        curl_multi_close($queue);
        return $responses;
    }


    /**
     * 仅推送至vera管理员的信息，目前用于抢票新增及扫码新增活动后推送至管理员尽快审核
     * @param string $msg
     * @return bool|int 返回成功推送至几位管理员
     */
    public static function pushToVeraAdmin($msg = '')
    {
        $db = Vera_Database::getInstance();
        $openIds = $db->select('cms_Admin', 'openid');
        $content['type'] = 'text';
        $content['data']['content'] = $msg;
        $func = new Data_Push_Func($content);
        $count = 0;
        foreach($openIds as $openid) {
            if (!empty($openid['openid'])) {
                $count += $func->push($openid['openid']);
            }
        }
        return $count;
    }

}

?>
