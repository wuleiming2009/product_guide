<?php
class Daemon_Crontab
{
    // 构造方法声明为private，防止直接创建对象
    private function __construct()
    {

    }
    // 阻止用户复制对象实例
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    
    public function test_crontab($args)
    {
        print_r($args);
    }
    
    //设定延迟 delay 秒呼叫函式 func. 而 args 当作参数传入 func 中.
    public function call_out( $func, $delay,  $args)
    {
        $delay = intval($delay);
        if (empty($args)) $args = null;
        if (empty($delay)) $delay = 0;
        //swoole_timer_after(3000, function () {
        //    echo "after 3000ms.\n"
        //});

        //放入队列
        $db = new Object_databased();
        $db->init();
        $table = DB_DATABASE.".crontab_q";
        
        //这里额外的统一使用mysql时间 用来作为判定 以便多个web主机的 callouts 能够同步
        $sql = "insert into ".$table." set func='".addslashes($func)."', args='".addslashes(json_encode($args))."', taskId=0,runtime=now() + INTERVAL ".$delay." SECOND";
        
        $ret = $db->exec($sql);
        if (is_string($ret)) return -1;
        if (!$ret) return -1;
        
        if ($ret) return $db->last_insert_id();
    }
    
    
    
    
    //每秒处理300条记录
    public function scan_tick( $serv, $fd )
    {
        //每秒处理300条数据
        $db = new Object_databased();
        $db->init();
        $task_id = $serv->worker_id;
        $task_num = $serv->setting['task_worker_num'];
        if (empty($task_num)) $task_num = 1;
        
        
        $table = DB_DATABASE.".crontab_q";
        //遍历所有 callouts
        if (empty($func_name)){
            $sql = "select id, taskid, func, args from ".$table." where status=0 and runtime <= now() order by runtime limit ".$task_num;
        }else{
            $sql = "select id, taskid, func, args from ".$table." where status=0 and runtime <= now() and func='".$func_name."' order by runtime limit ".$task_num;
        }
        
        $rows = $db->query($sql);
        if ( is_array($rows) && sizeof($rows) ) {
            //保存id编号
            $ids = array();
            foreach($rows as $row){
                $ids[] = $row["id"];
                //交给发送程序
                $taskArgs = array( "action"=> $row["func"], "data" => json_decode($row["args"],true), "module"=> "callouts", "id" => $row["id"]);
                $serv->task( $taskArgs, -1);
            }
            
            //更新状态
            $sql = "update ".$table." set status='1' where id in (".implode(",",$ids).")";
            $db->exec($sql);
        }
        
        $db->close();
        return;
    }





    //onTask
    public function do_call_out( $serv, $task_id, $src_worker_id, $taskArgs )
    {
        //$taskArgs = array( "action"=> $func, "data" => $args, "module"=> "callouts", "ids" => $ids);
        if (!$taskArgs["action"]) return 0;
        $func = $taskArgs["action"];
        $args = $taskArgs["data"];
        
        $startTime = microtime();
        $times = explode(" ",$startTime);
        $start1 = $times[0];
        $start2 = $times[1];
        //0.61468700 1490693966
        $func( $args);          //执行实际的功能
        
        $endTime = microtime();
        $times = explode(" ",$endTime);
        $end1 = $times[0];
        $end2 = $times[1];
        
        $timelong = ($end2 - $start2) + ( $end1 - $start1);
        if ($timelong >=  0.5){
            echo "Too long evalcost time: do_call_out[".$func."]=".$timelong."\n";
        }
        
        return $taskArgs;       //把数据传递回
        //task finish
    }
    
    
    
    //onFinish
    public function call_out_done( $serv, $task_id, $taskArgs )
    {
        $id = $taskArgs["id"];
        //更新状态 发送完毕
        $db1 = new Object_databased();
        $db1->init();
        $table = DB_DATABASE.".crontab_q";
        $sql = "delete from ".$table." where id = '".$id."'";
        $db1->exec($sql);
        $db1->close();
        
        return;
    }
}
