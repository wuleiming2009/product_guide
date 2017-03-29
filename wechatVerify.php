<?php
include_once('./include/globle.php');

Daemon_Efuns::log_event("wechatVerify", "[wechatVerify.php]".json_encode($_GET));

$signature = $_GET["signature"];
$timestamp = $_GET["timestamp"];
$nonce = $_GET["nonce"];
$echostr = $_GET["echostr"];

$result = false;

$result = Daemon_wechatMod::initMsg($data);

if ($result) {
	echo($echostr);
}
