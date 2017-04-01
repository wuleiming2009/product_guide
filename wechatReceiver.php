<?php
include_once('./include/globle.php');

Daemon_Efuns::log_event("wechatReceiver", "[wechatReceiver.php]".json_encode($_GET));

//echo($_GET["code"]." | ".$_GET["state"]."\n");
$code = $_GET["code"];
$appid = "wx0f55be4f4b73612a";
$appSecret = "b04b2c5b888d0defd6b0cee3f1ff41c8";
$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appSecret&code=$code&grant_type=authorization_code";

//初始化
$ch = curl_init();
//设置选项，包括URL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
//执行并获取HTML文档内容
$output = curl_exec($ch);


$jsonData = json_decode($output);
if ($jsonData->errcode) {
    print_r("登录失败");
    return;
}

$access_token = $jsonData->access_token;
$openid = $jsonData->openid;
$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";

//设置选项，包括URL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
//执行并获取HTML文档内容
$output = curl_exec($ch);

//释放curl句柄
curl_close($ch);
//打印获得的数据
print_r($output);
