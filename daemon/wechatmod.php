<?php

include_once "../include/wechat/wxBizMsgCrypt.php";

/**
 * Class Daemon_wechatMod 微信数据处理中心
 */
class Daemon_wechatMod
{

    // 构造方法声明为private，防止直接创建对象
    private function __construct() {}
    // 阻止用户复制对象实例
    public function __clone() { trigger_error('Clone is not allowed.', E_USER_ERROR); }


    private static $appId = "wx0f55be4f4b73612a";
    private static $token = "wuleiming";
    private static $encodingAesKey = "8C97HI49UQV9Wmp8j1frBYL95aHoD5ap3Jnht35scyi";
    private static $appSecret = "b04b2c5b888d0defd6b0cee3f1ff41c8";
    private static $text = "<xml><ToUserName><![CDATA[oia2Tj我是中文jewbmiOUlr6X-1crbLOvLw]]></ToUserName><FromUserName><![CDATA[gh_7f083739789a]]></FromUserName><CreateTime>1407743423</CreateTime><MsgType><![CDATA[video]]></MsgType><Video><MediaId><![CDATA[eYJ1MbwPRJtOvIEabaxHs7TX2D-HV71s79GUxqdUkjm6Gs2Ed1KF3ulAOA9H1xG0]]></MediaId><Title><![CDATA[testCallBackReplyVideo]]></Title><Description><![CDATA[testCallBackReplyVideo]]></Description></Video></xml>";

    /**
     * 初始化信息
     * $signature   签名
     * $timestamp   时间戳
     * $nonce       随机数
     */
    public static function initMsg( $signature, $timestamp, $nonce ) {

        $verifyArr = [ self::$token, $timestamp, $nonce ];
        sort( $verifyArr, SORT_STRING );
        $tempString = $verifyArr[0].$verifyArr[1].$verifyArr[2];
        $verifyString = sha1($tempString);

        Daemon_Efuns::log_event("wechatVerify", "initMsg:$signature | $verifyString";

        if ( $verifyString == $signature) {
            return true;
        }else{
            return false;
        }
    }
}



