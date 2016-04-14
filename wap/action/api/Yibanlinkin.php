<?php
/**
*
*    @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*    All rights reserved
*
*    file:            YibanLinkin.php
*    description:     易班绑定入口
*
*    @author Yuri
*    @license Apache v2 License
*
**/

/**
*  易班帐号绑定
*/
class  Action_Api_Yibanlinkin extends Action_Base
{

    function __construct()
    {

    }

    public function run()
    {
        if (isset($_SESSION['yb_user_info']['access_token']))
        {
            if (isset($_SESSION['openid']) && !empty($_SESSION['openid']))
            {
                Data_Db::linkYiban($_SESSION['yb_user_info']['yb_studentid'], $_SESSION['yb_user_info']['yb_userid'], $_SESSION['yb_user_info']['access_token'],$_SESSION['yb_user_info']['token_expires']);
                //if ($_SESSION['yb_user_info']['yb_userid'] == '1596251')
                  //Vera_Log::addLog('tmp', date('Y-m-d H:i:s') . ' ' . Vera_Database::getLastSql());
                Data_Db::updateYiban($_SESSION['openid'], $_SESSION['yb_user_info']['userid']);//更新vera_User表里的易班id,
                Vera_Log::addNotice('YibanLinkin', $_SESSION['yb_user_info']['userid'] . '绑定易班');
                header("Location:/wap/linkin?openid={$_SESSION['openid']}");
            }
            else
            {
                header("Location:http://q.yiban.cn/app/index/appid/1530");
                exit();
            }
            return true;
        }
        header("Location: /yiban/EntryFromYiban?appName=wap/api/Yibanlinkin");
        exit();

        //   	$conf = Vera_Conf::getConf('yiban');
		// $conf = $conf['nova'];
		
  //       Vera_Autoload::changeApp('yiban');
  //       $config = array(
  //           'appid' => $conf['AppID'],
  //           'seckey' => $conf['AppSecret'],
  //           'backurl' => $conf['CALLBACK']
  //           );
  //       $au = new Library_Ybapi_Authorize($config);
  //  		if (isset($_GET["code"]) && !empty($_GET['code']))
		// {
		// 	$info = $au->querytoken($_GET['code']);
		// 	if (isset($_SESSION['openid']) && !empty($_SESSION['openid']) && isset($info['access_token']) && isset($info['userid']) && isset($info['expires'])) 
		// 	{
  //               Vera_Autoload::reverseApp();
		// 		Data_Db::linkYiban($info['userid'], $info['access_token'], date("Y-m-d H:i:s",$info['expires']));//vera_Yiban表里更新或新增一条记录
		// 		Data_Db::updateYiban($_SESSION['openid'], $info['userid']);//更新vera_User表里的易班id
       
  //       		header("Location:/wap/linkin?openid={$_SESSION['openid']}");
  //       		return true;
		// 	}
  //           else
  //           {
  //               header("Location:http://q.yiban.cn/app/index/appid/1530");
  //           }
		// 	return true;
    }
} 

?>
