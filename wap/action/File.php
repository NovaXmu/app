<?php
class Action_File extends Action_Base
{
	function __construct (){}

	public function run(){
		$logDir = SERVER_ROOT . 'log/' . $GLOBALS['APP_NAME'];
		$file = $logDir . "/notice.log";
		$file = fopen($file, "r");
		$match = "together";
		$i = 0;
		while($file && !feof($file))
		{
			$row = fgets($file);
			preg_match_all("/\[[\s\S]*?\]/", $row,$tem);
			$tem = $tem[0];
			//print_r($tem);
			if(isset($tem[4]) && strpos($tem[4], $match))
			{
				$tem[4] = urldecode($tem[4]);
				$res[$i]['time'] = $tem[1];
				$res[$i]['remoteIP'] = $tem[3];
				$res[$i]['requestURI'] = $tem[4];
				$i++;
			}
		}
		$ip = $this->getIpNum(array_column($res,"remoteIP"));
		$depart_place = $this->getMatch(array_column($res,"requestURI"),"depart_place");
		$arrive_place = $this->getMatch(array_column($res,"requestURI"),"arrive_place");
		$waitingtime = $this->getMatch(array_column($res,"requestURI"),"waitingtime");
		$pc = $this->getMatch(array_column($res,"requestURI"),"pc");
		echo("访问数：" . $ip);
		echo "<br>出发地点：";
		print_r($depart_place);
		echo "<br>到达地点：";
		print_r($arrive_place);
		echo "<br>等待时间：";
		print_r($waitingtime);
		echo "<br>拼车情况：";
		print_r($pc);
	}
	
	public function getIpNum($arr){
		$ret = array_flip(array_flip($arr));
		return count($ret);
	}

	public function getMatch($arr, $match){
		$ret = NULL;
		foreach ($arr as $key => $value) {
			if(strpos($value, $match) !== false)
			{
				
				$match_preg = "/" . $match . "=[\s\S]*?&|" . $match . "=[\s\S]*?\]/";
				preg_match($match_preg, $value, $str);
				if(!$str)
				{
					continue;
				}
				$key = explode("=", $str[0]);
				$key = substr($key[1], 0, -1);
				preg_match("/\d{11}/", $value, $contact);
				if(!$contact){
					continue;
				}
				$contact = $contact[0];
				if(isset($flag[$contact]))
				{
					$key = $flag[$contact];
					$ret[$key]--;
				}
				$flag[$contact] = $key;//以手机号来区别身份
				if(isset($ret[$key]))
				{
					$ret[$key]++;
				}
				else
				{
					$ret[$key] = 1;
				}
			}
		}
		asort($ret);
		return $ret;
	}
}
?>
