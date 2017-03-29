<?php

/**
 * Class Daemon_wechatMod 微信数据处理中心
 */
class Daemon_wechatMod
{

    // 构造方法声明为private，防止直接创建对象
    private function __construct() {}
    // 阻止用户复制对象实例
    public function __clone() { trigger_error('Clone is not allowed.', E_USER_ERROR); }

    /**
     * 初始化信息
     * $signature   签名
     * $timestamp   时间戳
     * $nonce       随机数
     */
    public static function initMsg( $signature, $timestamp, $nonce ) {
        return true;
    }
}



