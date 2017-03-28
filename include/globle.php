<?php
//定义配置
$iniGameConf = parse_ini_file("/home/www/mu77game.ini",true);
//搜索本服所在的配置
foreach($iniGameConf as $serverId => $serverOne){
    if ($serverId == 'GLOBAL'){
        $gMyServerGlobal = $serverOne;
    }
    if ( $serverOne["server_name"] == $_SERVER["HTTP_HOST"] ){
        
        $gMyServer = $serverOne;
        $gMyServer["serverId"] = $serverId;
        break;
    }
}
if (empty($gMyServerGlobal)) exit();
if (empty($gMyServer)) $gMyServer = array();
//复制合并  相同KEY由myserver覆盖
foreach($gMyServer as $key=> $value) {
    $gMyServerGlobal[$key] = $value;
}

//定义配置
define ("HOME_DIR",         '/home/server/www/'.$_SERVER['HTTP_HOST'].'/');


//Define autoloader
function __autoload($class) {


    if (class_exists($class, false) || interface_exists($class, false)) {
        return true;
    }
    $className = ltrim($class, '\\');
    $file = HOME_DIR. str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    $file = strtolower($file);
    if ( preg_match('/[^a-z0-9\\/\\\\_.:-]/i', $file) ) {
        throw new Exception('Security check: Illegal character in filename');
        return false;
    }
    if ( file_exists($file) ) {
        require_once $file;
        return true;
    }
    //抛出异常
    throw new Exception('class : '. $class.' can not found');
    return false;
}

session_start();

date_default_timezone_set("Asia/Chongqing");

define ("SERVER_ID",        $gMyServerGlobal["server_id"]);
define ("LOG_DIR",          $gMyServerGlobal["log_dir"]);
define ("DAEMON_DIR",       HOME_DIR.'daemon/');
define ("OBJ_DIR",          HOME_DIR.'object/');
define ("VERIFY_URL",       $gMyServerGlobal["verify_url"]);
define ("SANDBOX_URL",      $gMyServerGlobal["sandbox_url"]);
define ("DB_DATABASE",      $gMyServerGlobal["DB_DATABASE"]);                   //GMTOOL库配置
define ("DB_HOST",          $gMyServerGlobal["DB_HOST"]);                       //MYSQL服务地址
define ("DB_PORT",          $gMyServerGlobal["DB_PORT"]); 
define ("DB_NAME",          $gMyServerGlobal["DB_NAME"]);                       //用户名
define ("DB_PASS",          $gMyServerGlobal["DB_PASS"]);                       //密码
define ("DB_PREX",          $gMyServerGlobal["DB_PREX"]);                       //决定MYSQL的存储区分
define ("DB_LOG",           $gMyServerGlobal["DB_LOG"]);                        //日志配置表
define ("DB_PAY",           $gMyServerGlobal["DB_PAY"]);                        //计费表

//游戏服 数据访问配置
define ("DB_BAK_USER",      $gMyServerGlobal["game_bak_user"]);                 //游戏服通用 用户名
define ("DB_BAK_PASS",      $gMyServerGlobal["game_bak_pass"]);                 //游戏服通用 密码


//NOSQL存储支持 memcached / memcache / ttdb / mangodb
define ("NOSQL_TYPE",           $gMyServerGlobal["NOSQL_TYPE"]);
define ("TT_SERVER",            $gMyServerGlobal["TT_SERVER"]);
define ("TT_PORT",              $gMyServerGlobal["TT_PORT"]);
//REDIS配置
define ("REDIS_HOST",           $gMyServerGlobal["REDIS_HOST"]);                        //Redis服 hostname
define ("REDIS_PORT",           $gMyServerGlobal["REDIS_PORT"]);                        //Redis 端口
define ("REDIS_TYPE",           $gMyServerGlobal["REDIS_TYPE"]);                        //类型  1单点 2M/S主从 3集群(3.0标准)

//游戏架构默认配置
define ("SERVER_PREX", 1000000000);


define ("HE_LOG",               $gMyServerGlobal["he_log"]);            //= /home/www/data/datacenter 
define ("HE_ENTER",             $gMyServerGlobal["he_enter"]);          //= /home/www/data/he_enter_sh
define ("DC_OPTIONS",           $gMyServerGlobal["dc_options"]);          //= /home/www/data/dc_options
define ("ERROR_LOG",            $gMyServerGlobal["dc_err_log"]);          //= /home/www/data/dc_err_log

//GeoIP本地库配置
define ("GeoIP_DATA",           $gMyServerGlobal["GeoIP_DATA"]);                        //ip库

//卡片通知接口
define ("NOTICE_LIST",          $gMyServerGlobal["notice_url"]);