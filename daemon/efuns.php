<?php
include_once(dirname(__FILE__).'/dbase.php');
//include_once(dirname(__FILE__).'/plistpharse.php');

class dict extends Daemon_Dbase
{
    private $euid;
    protected function seteuid( $uid)
    {
        $this->euid = $uid;
        return;
    }

    public function geteuid()
    {
        return $this->euid;
    }

    public function query_save_id()
    {
        return $this->euid;
    }
    
    public function __construct()
    {
    }
}

//需优先加载  globals.php
final class Daemon_Efuns
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
    
    //日志
    public static function log_event($type,$param, $strSeed=0)
    {
        $fileDir=LOG_DIR;
        if (empty($fileDir)) return 0;
        
        $fileName = $type.date("Ymd",time()).".log";
        $remote_ip = $_SERVER["REMOTE_ADDR"];
        //custom index
        if ( !$strSeed ){
            $strTime = microtime();
            $strSeed = substr($strTime,2,6);
            $intSec = time() - mktime(0,0,0,date("m"),date("d"),date("Y"));
            $strSeed = $intSec.$strSeed;
        }
        
        $content = '';
        if(is_string($param)){
            $content = $param;
        }else {
            while ( list( $key, $val ) = each( $param ) ){
                if (is_array($val) || is_object($val)) $val = serialize($val);
                $content .= $key.":".$val."||";
            }
        }
        list($usec, $time) = explode(" ", microtime());
        $rText = "[$strSeed]".date("Y-m-d H:i:s",$time).".$usec||$remote_ip||".$content;
        if(!file_exists($fileDir))
        {
            mkdir($fileDir,0777);
        }
        if($fo = @fopen($fileDir.$fileName,"a+"))
        {
            fwrite($fo,$rText."\r\n");
            fclose($fo);
        }
        return $strSeed;
    }
    
    //输出错误
    public static function error_code( $err_code , $err_msg = "")
    {
        $result = json_encode(array("result" => $err_code, "errmsg" => $err_msg));
        echo $result;
        //TODO  错误日志记录
        return $result;
    }
    

    //输出缓存
    public static function data_push( $result)
    {
        if (is_array($result)){
            $result["result"] = 0;
            $result["errmsg"] = "";
            //$result = Daemon_Efuns::array_to_string($result);
            $ret = json_encode($result);
        }else{
            $ret = array();
            $ret["result"] = 0;
            $ret["errmsg"] = "";
            $ret["msg"] = $result;
            $ret = json_encode($ret);
        }
        echo $ret;
        return $ret;
    }

    public static function debug_data($result)
    {
        $ret = array( "ret" => $result );
        return json_encode( $ret );
    }
    
    public static function data_compress_push( $result )
    {
        $compress = gzencode($result);
        $userSaveData = base64_encode($compress);
        echo $userSaveData;
        exit();
    }
    
    //加锁加密数据
    public static function data_compress_to( $result )
    {
        $compress = gzencode($result);
        $userSaveData = base64_encode($compress);
        return $userSaveData;
    }
    
    
    
    //按用户名 name 查找用户，返回用户对象
    public static function find_player( $name )
    {
        $db = new Object_databased();
        $db->init();
        $tableUser = "user";

        $cols = array('id');
        $where = array('name' => $name);
        $rows = $db->select($tableUser,$cols)->where($where)->query();
        if (is_string($rows)){
            return 401;
        }
        if (!$rows || !sizeof($rows)){
            return 6001;
        }
        $targetId = $rows[0]['id'];

        $ob = new Object_User($targetId);
        if ($ob->restore()) {
            return $ob;
        }
        return 0;
    }
    
    
    
    
    /**
     *   自能是中文简体 英文  和数字
     *   检查字符串 屏蔽字
     *   返回1有效 / 0无效
     */
    public static function checkStr($str , $len)
    {
        if ($len < 2) $len = 2;
        mb_regex_encoding("UTF-8");
        if (!mb_ereg("^[,，.。\w]{1,$len}$", $str)) {
            return 0;
        }
        
        $db = new Object_databased();
        $db->init();

        $sql = "select count(*) from ".DB_CENTER.".badword where '".mysql_real_escape_string($str)."' like concat(concat('%',word),'%')";
        $rows = $db->query($sql);
        
        if (!is_array($rows)) return 0;
        if ( $rows[0]["count(*)"] ) return 0;
        return 1;
    }

    /*
     *   过滤特殊字符
     *   返回修正后的串
     */
    public static function filterStr($str)
    {
        $db = new Object_databased();
        $db->init();
        
        $sql = "select word from ".DB_CENTER.".badword where '".mysql_real_escape_string($str)."' like concat(concat('%',word),'%')";
        $rows = $db->query($sql);
        
        if (!is_array($rows)) return $str;
        if (sizeof($rows) == 0) return $str;
        
        foreach($rows as $row){
            $str = str_replace($row["word"],'**',$str);
        }
        return $str;
        //return Daemon_CMod::shield_filtrate($str);
    }
    
    //剩余时间 字符串模式
    public static function restTime2str( $times )
    {
        $hours = floor($times / 3600);
        $times = $times % 3600;
        $minus = floor($times / 60);
        $second = $times % 60;
        if ($hours < 10) $hours = "0".$hours;
        if ($minus < 10) $minus = "0".$minus;
        if ($second < 10) $second = "0".$second;
        return ($hours.":".$minus.":".$second);
    }
    
    
    //扁平化输出
    public static function array_to_string( $result )
    {
        $lines = array();
        foreach($result as $one){
            $lines[] = implode(",",$one);
        }
        return implode("\n",$lines);
    }
    
    //将 |,格式 解析成数组
    public static function string_decode_array( $result )
    {
        $rows = explode("|", $result);

        $info = array();
        if (is_array($rows)){
            foreach($rows as $row){
                $tabs = explode(",", $row);
                $reward = array();
                if (sizeof($tabs) == 2){
                    $reward = array(
                        "id" => $tabs[0],
                        "num" => $tabs[1],
                        );
                }else if (sizeof($tabs == 1)){
                    if (sizeof($rows) == 1 ){       //单项
                        return $result;
                    }else {
                        $reward = array(
                            "id" => $tabs[0],
                            "num" => 0,
                        );
                    }
                }
                $info[] = $reward;
            }
        }
        return $info;
    }
    
    
    //解析cocos2dx导出的plist文件
    public static function plist_to_array( $plist )
    {
        $dict = new dict();
        $lines = explode("\n",$plist);
        $node = array();            //buildings 1
        $key = '';
        $arrIndex = 0;
        
        $inProcessValue = 0;
        $inProcessKey = 0;
        $inProcessArray = 0;
        
        $plistKeyTypes = array("string", "integer", "real");
        
        foreach($lines as $line){
            $line = trim($line);
            
            if ($line == '<dict>'){             //buildings 1/2
                if ( $inProcessArray ){
                    //数组成员 Key++
                    $node[] = $arrIndex;
                    $arrIndex++;
                    $key = '';
                }else {
                    //非数组成员
                    if ($key!=''){
                        $node[] = $key;
                    }
                    $key = '';
                }
            }
            
            //<key> 开始 然后找到第一对 </key>结束
            if (strstr($line,'<key>') || $inProcessKey ){                             //key=2
                //开始
                if (!$inProcessKey){
                    $line = str_replace('<key>','',$line);
                    $key = "";
                    $inProcessKey = 1;
                }
                //结束
                if (strstr($line,'</key>')){
                    $line = str_replace('</key>','',$line);
                    $key .= $line;
                    $inProcessKey = 0;
                }else {
                    $key .= $line;
                    continue;
                }
            }
            
            //数组
            if ($line == "<array>"){
                $inProcessArray = 1;
                if ($key!=''){
                    $node[] = $key;
                }
                $arrIndex = 0;
                $key = '';
            }
            if ($line == "</array>"){
                $inProcessArray = 0;
                $arrIndex = 0;
                //====做个特殊标记 表明这是一个纯Array====
                $key = '__isArray';
                if (sizeof($node)){
                    $nodeKey = implode("/",$node);
                    $nodeKey .= '/'.$key;
                }else{
                    $nodeKey = $key;
                }
                $dict->set($nodeKey, 20150205);
                //=================标记结束===============
                array_pop($node);       //弹出一个KEY
                $key = '';
            }
            
            
            //
            $isMatched = 0;
            foreach($plistKeyTypes as $pType){
                // <string></string>
                if (strstr($line,'<'.$pType.'>') || ( $inProcessValue && $inProcessType == $pType ) ){
                    $isMatched = 1;
                    
                    if (!$inProcessValue){
                        $line = str_replace('<'.$pType.'>','',$line);
                        $inProcessValue = 1;
                        $inProcessType = $pType;
                        $value = "";
                    }
                    
                    if (strstr($line,'</'.$pType.'>')){
                        $line = str_replace('</'.$pType.'>','',$line);
                        $value .= $line;
                        $inProcessValue = 0;
                        $inProcessType = "";
                    }else {
                        $value .= $line;
                        break;
                    }
    
                    if (sizeof($node)){
                        $nodeKey = implode("/",$node);
                        $nodeKey .= '/'.$key;
                    }else{
                        $nodeKey = $key;
                    }
                    $dict->set($nodeKey, $value);
                    $key = '';
                    //匹配完成，则无需继续
                    break;
                }
            }
            if ($isMatched) continue;
            
            
            //bool  <true/>
            if ($line == '<true/>' || $line == '<false/>' ){
                if ($line == '<true/>')
                    $value = 1;
                else
                    $value = 0;

                if (sizeof($node)){
                    $nodeKey = implode("/",$node);
                    $nodeKey .= '/'.$key;
                }else{
                    $nodeKey = $key;
                }
                $dict->set($nodeKey, $value);
                $key = '';
            }
            

            //封闭一个dict
            if ($line == '</dict>'){
                array_pop($node);
                $key = '';
            }
                
            //封闭一个空的dict 关闭key->dict
            if ($line == '<dict/>'){
                $node[] = $key;
                $nodeKey = implode("/",$node);
                $dict->set($nodeKey, array());
                array_pop($node);
                $key = '';
            }
            
            if ( $line == '</plist>'){
                return $dict->query_entire_dbase();
            }
            
        }
    }
    
    
    //将array导出成 plist内容
    public static function deep_array_to_plist( $dict , $deep = 1)
    {
        if (!is_array($dict) || !sizeof($dict)) return "</dict>";
        $tabs = str_repeat("    ", $deep); 
        
        $arrayType = 'dict';
        if (is_array($dict) && isset($dict["__isArray"]) && $dict["__isArray"]==20150205 ){
            $arrayType = 'array';
        }
        
        $str = $tabs.'<'.$arrayType.'>'."\n";                
        foreach($dict as $key => $value){
            
            if ($key === "__isArray" && $value === 20150205) continue;
            
            //dict需要Key 数组不需要
            if ($arrayType == 'dict') {
                $str .= $tabs.'    <key>'.$key.'</key>'."\n";
            }
            //如果子对象是DICT 进入下一次循环
            if (is_array($value)){
                if (!sizeof($value)){
                    $str .= $tabs.'    <dict/>'."\n";
                }else {
                    $res = Daemon_Efuns::deep_array_to_plist( $value , $deep + 1);              //返回 <array>...</array>
                    $str .= $res;
                }
            }else {
                $str .= $tabs.'    <string>'.$value.'</string>'."\n";
            }
        }
        $str .= $tabs.'</'.$arrayType.'>'."\n";
        return $str;
    }
    
    public static function array_to_plist( $dict )
    {

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd"/>

<plist version="1.0">'."\n\n";
        $str .= Daemon_Efuns::deep_array_to_plist( $dict );
        $str .= "\n\n".'</plist>';

        //$plist = new PropertyList($dict);
        //return $plist->text();
        return $str;
    }
    
    
    
    
    
    
    
    //计算汇率
    public static function huilv()
    {
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/currencyservice/type';
        $header = array( 'apikey' => '89534861f893afc4bf437609410be0be' );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        return json_decode($res);
    }
}








