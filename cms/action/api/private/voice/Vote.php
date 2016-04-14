<?php
class Action_Api_Private_Voice_Vote extends Action_Base
{
    private function _login($id, $passwd) {
        $api = 'http://va.yiban.cn/api.php?a=login';
        $url = $api. '&account=' .$id. '&pwd=' .$passwd;

        $handle = curl_init();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_COOKIEJAR      => "",
            );

        curl_setopt_array($handle, $options);

        $content = curl_exec($handle);//执行

        $content = json_decode($content, true);
        if($content['code'] == 1000) {
            return $handle;
        }
        else {
            echo "<br>Login with ".$id." Failed!<br><br>";
            return false;
        }
    }

    private function _vote($handle, $voteId) {
        $api = 'http://va.yiban.cn/vote.php';
        $url = $api. '?toid=' . $voteId;

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            );

        curl_setopt_array($handle, $options);

        $content = curl_exec($handle);//执行
        $json = json_decode($content, true);
        if($json['err'] == '0' && $json['remain'] >= 0) {
            return '0';
        }
        else if($json['err'] == '5717') {//已投满5次
            return '5717';
        }
        else if ($json['err'] == '5718') {//不能重复投票
            return '5718';
        }
        else {
            echo "<br><br>";
            var_dump($content);
            return false;
        }
    }

    private function _autoVote($phone, $passwd){
        $errCount = 0;
        $handle = $this->_login($phone, $passwd);
        if (!$handle) {
            return 4;
        }

        $id = array('5069686','876059','5200864','5081965','5194560');

        for ($i=0; $i < 5; $i++) {

            $result = $this->_vote($handle,$id[$i]);

            if ($result == '0') {
            }
            else if ($result == '5717') {//已投满五次
                $errCount = 1;
                //return $errCount;
            }
            else if ($result == '5718') {//已投过这个人
                $errCount = 2;
            }
            else {
                echo "vote for ". $id[$i] ." failed<br><br>";
                $errCount = 3;
            }
        }
        return $errCount;
    }

    public function run(){
        //$start = time();
        $start = date("H:i:s");
        $today = "08:05:00";
        $flag = floor((strtotime($start) - strtotime($today))/600);
        if($flag < 0)
        {
            echo "现在不在投票时间范围内";
            return false;
        }
        $path = SERVER_ROOT . '/app/cms/action/api/private/voice/data.csv';
        //$path = 'data.csv';
        $fp =fopen($path, 'r');
        $user = array();
        $i = 0;
        while (($line = fgets($fp)) !== false) {
        $temp = explode(',', $line);
        $user[$i]['phone'] = $temp[0];
        $user[$i]['pwd']= $temp[1];
        $i++;
        }
        fclose($fp);
        
        //23:30左右全部账号刷一遍
        if($start>"23:25:00" && $start<"23:40:00")
        {
            $start_point = 0;
            $every = $i - $start_point;//此次投票所有账号都投
            
        }
        else{
            $every = floor($i/90);//每10分钟投的账号数
            $start_point = $flag*$every;//此次投票账号的起点
        }
       
        if($start_point > $i)
        {
            echo "投完啦";
            return false;
        }
        $flag++;
        echo "这是第". $flag ."次投票<br>" . "本次投票起点：" . $start_point . "<br>本次投票的账号数：" . $every;
        $j = 0;
        $total = 0;
        while($start_point <= $i&& $j < $every)
        {
           $errCount = $this-> _autoVote($user[$start_point + $j]['phone'], $user[$start_point + $j]['pwd']);
           //var_dump($user[$start_point + $j]);
           if($errCount != 0 )
                {                       
                    $total++;
                    echo "<br>" . $total . "<br>";
                    echo "error count : ". $errCount;
                    echo "<hr>";
                }
           $j++;
        }
        $effective = $every - $total;
        echo "<br>本次投票有效的账号数为：" . $effective;
        $end = date("H:i:s");

        echo "<br><br>";
        echo "cost time : ". (strtotime($end) - strtotime($start)) ."秒";
        if ($total == 0) {
           echo "<br>All is well!";
          }
    }
}
?>
