<?php

$appid = "wx0f55be4f4b73612a";
$redirect_uri = urlencode("http://wlm.x-four.cn/wechatReceiver.php");
$state = "a_test";
$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";

print_r($url."\n");