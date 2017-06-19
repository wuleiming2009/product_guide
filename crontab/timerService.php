<?php

$serverName = $argv[1];         //所在域名
if (empty($serverName)){
    echo "\nphp messagePushService.php <serverName>";
    echo "\n[serverName     = dc.mu77.com\n\n";
    exit();
}

$globleFile = '/home/server/www/'.$serverName.'/include/globle.php';
if ( !file_exists($globleFile) ) {
    echo "$globleFile is not exists\n";
    exit();
}
$_SERVER["HTTP_HOST"] = $serverName;
include_once($globleFile);




//========================================================================
//启动一个服务
$serv = new Swoole\Server("127.0.0.1", 9501);
$serv->set(array(
    'worker_num' => 1,                              //工作进程数量  每个群发类型分别处理
    'task_worker_num' => 50,                        //TASK进程数量
    'daemonize' => true,                            //是否作为守护进程
    'log_file'  => '/home/logs/swoole_timer.log',
    'heartbeat_check_interval' => 60,               // 心跳检测间隔时长(秒)
    'heartbeat_idle_time'      => 300,              // 连接最大允许空闲的时间
));


$serv->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});
$serv->on('receive', function ($serv, $fd, $from_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});
$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});





//worker ========================================================================
//启动一个定时器
$serv->on('workerStart', function($serv, $worker_id){

    //只在worker中启动定时器, task不需要  每秒钟调用一下 丢出一个task
    if (!$serv->taskworker) {
        //启动一个tick循环
        //启动定时器扫描推送表      
        $serv->in_tick = 0;         //用来防止tick被叠加调用 延迟没有关系,叠加会导致数据处理重复
        $serv->tick(1000, function() use ($serv, $fd) {
            if ($serv->in_tick == 1) return;            
            
            $serv->in_tick = 1;
            //每秒处理300条数据
            Daemon_Crontab::scan_tick( $serv, $fd );
            $serv->in_tick = 0;
        });

        //启动防沉迷循环
        //Daemon_antiaddictionApp::startUploadLogic([]);
    }
});





//task ========================================================================
$serv->on('task',  function($serv, $task_id, $src_worker_id, $taskArgs){

    //这种写法 可以让功能模块和流程分开 方便热更新
    return Daemon_Crontab::do_call_out( $serv, $task_id, $src_worker_id, $taskArgs );
    
});




$serv->on('finish',  function($serv, $task_id, $taskArgs) {
    
    //这种写法 可以让功能模块和流程分开 方便热更新
    return Daemon_Crontab::call_out_done( $serv, $task_id, $taskArgs );
    
});



//START
$serv->start();
