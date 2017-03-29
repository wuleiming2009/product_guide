<?php
//定义配置
$iniGameConf = parse_ini_file("/home/www/xfour.ini",true);
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

define ("LOG_DIR",          $gMyServerGlobal["log_dir"]);
define ("DAEMON_DIR",       HOME_DIR.'daemon/');
define ("OBJ_DIR",          HOME_DIR.'object/');
define ("DB_DATABASE",      $gMyServerGlobal["DB_DATABASE"]);                   //GMTOOL库配置
define ("DB_HOST",          $gMyServerGlobal["DB_HOST"]);                       //MYSQL服务地址
define ("DB_PORT",          $gMyServerGlobal["DB_PORT"]); 
define ("DB_NAME",          $gMyServerGlobal["DB_NAME"]);                       //用户名
define ("DB_PASS",          $gMyServerGlobal["DB_PASS"]);                       //密码