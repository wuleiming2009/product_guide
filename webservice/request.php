<?php
include_once('../include/globle.php');

$inputArgs = $_REQUEST;
$seed = Daemon_Efuns::log_event("request",$inputArgs, $seed);
$_REQUEST['seed'] = $seed;

//验证MD5
$postUrl = $_SERVER['QUERY_STRING'];
$postMd5Pos = strstr($postUrl, "md5key");
$postUrl = str_replace("&".$postMd5Pos,"",$postUrl);
$md5Key = $_REQUEST['md5key'];

// if ( md5($postUrl."94fdB9SYWEQqaBA4hC3BhVwRiHs=") != $md5Key ){
//     $err_code = 32700;
//     Daemon_Efuns::error_code( $err_code );
//     exit();
// }

//请求分流
$module = $inputArgs['daemon'];
$action = $inputArgs['action'];
$arguments = array();

while ( list( $key, $val ) = each( $inputArgs ) ){
    if ($key == 'daemon') continue;
    if ($key == 'action') continue;
    
    $arguments["$key"] = $val;
}

//$module = login
$mod_file = DAEMON_DIR.$module.'app.php';
if ( file_exists($mod_file) ) {
    @include_once($mod_file);
}else {
    $err_code = 32601;
    return Daemon_Efuns::error_code( $err_code );
}

//Daemon_loginApp
$staticClassName = 'Daemon_'.$module.'App';
call_user_func( $staticClassName.'::'.$action, $arguments);
