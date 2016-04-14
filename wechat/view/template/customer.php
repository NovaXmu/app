<?php

/**
*
*   @copyright  Copyright (c) 2014 Yuri Zhang (http://www.yurilab.com)
*   All rights reserved
*
*   filename:       customer.php
*   description:    多客服信息回复模板
*
*   @author Yuri
*   @license Apache v2 License
*
**/

$view = "<xml>
            <ToUserName><![CDATA[".$data['ToUserName']."]]></ToUserName>
            <FromUserName><![CDATA[".$data['FromUserName']."]]></FromUserName>
            <CreateTime>".$data['CreateTime']."</CreateTime>
            <MsgType><![CDATA[transfer_customer_service]]></MsgType>
        </xml>";


?>
