<?php

/**
 * Class Daemon_wechatApp 微信数据处理中心
 */
class Daemon_wechatApp
{
    // 构造方法声明为private，防止直接创建对象
    private function __construct() {}
    // 阻止用户复制对象实例
    public function __clone() { trigger_error('Clone is not allowed.', E_USER_ERROR); }

}
