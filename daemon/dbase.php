<?php
//此类为基本继承，目前为 user.php object.php room.php服务
abstract class Daemon_Dbase
{
    private $dbase = array();               //save to db by ticktime
    private $tmpdbase = array();               //save to db by ticktime
	
	//此继承不允许单独成实例    
    private function __construct($euid)
    {
    }
    
    //此两个方法，必须具备，以便实例能被保存到memcached ，此两方法由上一级class使用，方便初始化物件
    abstract  protected function seteuid( $username);
    abstract  public function geteuid();
    abstract  public function query_save_id();
    
    protected function _query( $map, $parts )
    {
    	if (!is_array($map)) return 0;

    	$parts = explode('/',$parts);
    	if (!sizeof($parts)) $parts = array($parts);

    	$value = $map;
    	foreach($parts as $partone){
    	    if (!is_array($value)) return NULL;
    	    if ( array_key_exists($partone,$value ) ) {
    	        $value = $value[$partone];
    	    }else {
    	        return NULL;
    	    }
    	}
    	return $value;
    }
    
    protected function _delete( $map, $parts )
    {
        if (!is_array($map)) return 0;

        $parts = explode('/',$parts);
    	if (!sizeof($parts)) $parts = array($parts);

    	$str = "";
    	foreach($parts as $partone){
    	    $str .= "['".$partone."']";         //这里包含一个单引号，确保 $partone 总是不被再次解析 而仅仅是作为一个字符串
    	}
    	$evalstr = 'unset($map'.$str.');';      //这里使用单引号， $map不被解析
    	eval($evalstr);
    	return $map;
    }
    
    protected function _set( $map, $parts, $value )
    {
        if (!is_array($map)) return 0;
        $parts = explode('/',$parts);
    	if (!sizeof($parts)) $parts = array($parts);
    	
    	$temp = $map;
    	$j = sizeof($parts);
    	for($i = 0; $i < $j; $i++){
    	    $partone = $parts[$i];
    	    if ( array_key_exists($partone,$temp ) ) {
    	        $temp = $temp[$partone];
    	        if ( !is_array($temp) && ($i < $j-1) ) {         //路径上不能有叶子节点
    	            return 0;
    	        }
    	    }else break;            //
    	}
    	$str = "";
    	foreach($parts as $partone){
    	    $str .= "['".$partone."']";             //通过包含单引号 来防止注入
    	}
    	$evalstr = '$tmp = &$map'.$str.";";         //使用单引号 防止变量被解析
    	eval($evalstr);
    	$tmp = $value;
    	
    	return $map;
    }


    
    //查询某个值 如果这个值不存在，则返回0
    public function query($parts)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        $res = $this->_query( $this->dbase, $parts );

        return $res;
    }
    
    //删除某个值 如果删除失败，则返回0 否则返回1 ，如果删除的值不存在，也会被默认为成功，返回1
    public function delete($parts)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        $res = $this->_delete( $this->dbase, $parts );
        if (!$res) return 0;
        
        $this->dbase = $res;
        return 1;
    }
    
    //设置某个值 如果设置失败，则返回0 否则返回1 如果设置的路径上存在一个叶子（非数组值），就会导致失败
    // 比如  query("a/b/c") ==1 则 set("a/b/c/d/e") 就会失败  set并不会默认的把 query("a/b/c") 这个值清除掉。
    public function set($parts,$value)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        
        $res = $this->_set( $this->dbase, $parts, $value );
        
        if (!$res) return 0;
        
        $this->dbase = $res;
        return $res;
    }
    
    // 增加某个值， 如果原本这个值不存在，则等同于 set
    // 如果原来的值并非 int ，则会返回失败0 。如果添加成功，则返回 1， 添加失败则返回 0
    public function add($parts,$value)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        
        $oldvalue = $this->query( $parts );
        if ( is_array($oldvalue) ){
            $oldvalue[] = $value;
            $res = $this->set($parts,$oldvalue);
            return $res;
        }else if ( empty($oldvalue) || is_numeric($oldvalue) ){
            $value = $oldvalue + $value;
            $res = $this->set($parts,$value);
            return $res;
        }
        
        return 0;
    }
    
    
    /***
    temp_dbase的数据会被保存进入TT，但是不会随着object flush，被推送到客户端。
    当有些数据不期望出现在客户端时，可以使用temp来保存
    ****/
    
    //查询某个值 如果这个值不存在，则返回0
    public function query_temp($parts)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        $res = $this->_query( $this->tmpdbase, $parts );

        return $res;
    }
    
    //删除某个值 如果删除失败，则返回0 否则返回1 ，如果删除的值不存在，也会被默认为成功，返回1
    public function delete_temp($parts)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        $res = $this->_delete( $this->tmpdbase, $parts );
        if (!$res) return 0;
        
        $this->tmpdbase = $res;
        return 1;
    }
    
    //设置某个值 如果设置失败，则返回0 否则返回1 如果设置的路径上存在一个叶子（非数组值），就会导致失败
    // 比如  query("a/b/c") ==1 则 set("a/b/c/d/e") 就会失败  set并不会默认的把 query("a/b/c") 这个值清除掉。
    public function set_temp($parts,$value)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        
        $res = $this->_set( $this->tmpdbase, $parts, $value );
        
        if (!$res) return 0;
        
        $this->tmpdbase = $res;
        return $res;
    }
    
    // 增加某个值， 如果原本这个值不存在，则等同于 set
    // 如果原来的值并非 int ，则会返回失败0 。如果添加成功，则返回 1， 添加失败则返回 0
    public function add_temp($parts,$value)
    {
        if (substr($parts,-1) == "/") $parts  = substr($parts,0,strlen($parts)-1);
        
        $oldvalue = $this->query_temp( $parts );
        if ( is_array($oldvalue) ){
            $oldvalue[] = $value;
            $res = $this->set_temp($parts,$oldvalue);
            return $res;
        }else if ( empty($oldvalue) || is_numeric($oldvalue) ){
            $value = $oldvalue + $value;
            $res = $this->set_temp($parts,$value);
            return $res;
        }
        
        return 0;
    }
    
    
    //============== save and restore ==========================
    public function query_entire_dbase()
    {
        return $this->dbase;
    }
    
    public function set_entire_dbase($var)
    {
    	if (empty($var)) $var = array();
    	$this->dbase = $var;
    }
    
    
    public function query_entire_tmpdbase()
    {
        return $this->tmpdbase;
    }
    
    public function set_entire_tmpdbase($var)
    {
    	if (empty($var)) $var = array();
    	$this->tmpdbase = $var;
    }
    
}

