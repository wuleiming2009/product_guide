<?php
interface iDbTemplate
{
    public function init();                     //数据库初始化
    public function query($sql='', $server_id=0);                //按照$sql进行查询  return $rows = Array();
    public function exec($sql='', $server_id=0);                 //按照$sql进行执行  return affected_rows or mysqlErrorCode(String)
    public function last_insert_id();           //执行数据插入时的ID
    
    public function set_sql( $sql );                //自定义sql语句
    public function addon( $sql );              //在当前sql语句后，添加。常见的比如自定义where子句，则可以： $db->addon('where ....');
                                                //eg:  $db->sql("select * from table")->addon("where id<5")->query();
                                                // in,between等特殊的条件，请先用这种模式实现
    
    public function set_cols( $cols );                 //为insert/update 设置对应列
                                                        //as: insert into table (cols1, cols2...)
                                                        
    public function insert( $table, $cols );            //将$cols = array(col_name=>col_value) 形成标准insert sql
                                                        //as: insert into table (cols_key) 
                                                        //return affected_rows or mysqlErrorCode(String)
                                                
    public function update( $table, $cols , $escape = 1 );     //将$cols = array(col_name=>col_value) 形成标准update sql
                                                        //as: update table set cols_key=cols_value,...;
                                                        // eg: $db->update($cols)->set_where($cols)->query();
                                                        //return affected_rows or mysqlErrorCode(String)
    
    public function select( $table, $colsArray );       //输入字段名数组， == select col1,col2,col3 from table 
                                                        // eg: $db->select($cols)->query(); 
                                                        // return $db;
    
    public function delete( $table );                   //删除记录
    
    //                                                        
    public function where($cols, $type = 'and', $compare = '=', $escape = 1);            //字段赋值 where可以被应用在 update/insert/select之前
                                                        // eg: $db->select($cols)->where($cols)->query();
                                                        // return $db;       
                                                        // $type默认为and 也可以为 or          
                                                        // $compare 默认为= ，也可以是 '>', '<', '!=', 'like' 等其他合法运算符
                                                        // 简单来说 解析器总是 $type.' '.$cols_key.$compare.$cols_value
    //eg:  $db->select($table2, $cols2)->insert($table1,$cols1)->where($cols,"and")->query();
    // insert into table1 (cols1) select (cols2) from table2 where...
}

//db class
final class Object_databased implements iDbTemplate
{
    private $instance;
    // 构造方法声明为private，防止直接创建对象
    public function __construct(){}
    
    public $params = array();
    private $db_name;
    public $rows = array();
    private $sql;
    public $server_list = array();
    public $channel;
    private $game_db;
    
    public function _is_operator($str)
    {
        $str = trim($str);
        if ( !preg_match( "/(\s|<|>|!|=|like|is)/i",$str)){
            return 0;
        }
        return 1;
    }
    
    //维持一个独立的链接
    public function init() 
    {
        //建立连接，目前数据库连接维护，放在globe.php
        $mysqli = new mysqli(DB_HOST, DB_NAME, DB_PASS, DB_DATABASE, DB_PORT);
        $this->instance = $mysqli;
        if ($mysqli->connect_errno) {
            Daemon_Efuns::log_event("database","Failed to connect to MySQL: (" . $mysqli->connect_errno . ") error=".$mysqli->connect_error );
            return 0;
        }
        $this->db_name = DB_DATABASE;
        $mysqli->query("SET NAMES UTF8");
        return $this;
    }
    
    
    //初始化服务器列表 以便 init_game_db 更快地创建mysqli对象
    public function init_server_list( $channel = null )
    {
        if (empty($this->instance)) $this->init();
        $mysqli = $this->instance;
        //针对新版的数据中心,适用此方法
        $this->server_list = array();
        $sql = "select * from ".DB_DATABASE.".server_list";
        if ($channel){
            $sql = $sql." where channel='".$channel."'";
            $this->channel = $channel;
        }else{
            $this->channel = '';
        }
        
        if ($res = $mysqli->query($sql)){
	        if ( $mysqli->error ){
                Daemon_Efuns::log_event("database","sql=".$sql." error=".$mysqli->error);
                return 0;
            }
            
            $server_list = array();
	        if ($res && $res->num_rows){                                    //数组为有内容
	            while( $row = $res->fetch_assoc()){
	                if ( $row["server_type"] == 'payment'){
	                    $server_list[ "payment" ] = $row;  
	                }else{
	                    $server_list[ $row["server_id"]] = $row;  
	                }
	            }
	            $this->server_list = $server_list;
	        }else {         // 0
	            return 0;
	        }
	    }else {         // 0
	        return 0;
	    }
        
        return $this;
    }
    
    //创建某个server_id的链接
    public function init_game_db( $server_id ) 
    {
        $row = $this->select_server( $server_id );
        if ($this->game_db){
            $this->game_db->close();        //先关了 重新确立链接 不管是不是当前server_id的链接
        }
        
        //连接服务器
        if ($row["server_type"] == 'game'){
            $database_name = DB_PREX.$row["server_id"];
            $new_conn = new mysqli( $row["data_host"] , DB_BAK_USER , DB_BAK_PASS, $database_name,  $row["data_port"]);
            if ($new_conn->connect_errno){
                Daemon_Efuns::log_event("database","data_host=".$row["data_host"] ." connect_errno=".$new_conn->connect_error);
                return 0;
            }
        }else if ( $row["server_type"] == 'payment'){
            $database_name = "payment";
            $new_conn = new mysqli( $row["data_host"] , DB_BAK_USER , DB_BAK_PASS, $database_name,  $row["data_port"]);
            if ($new_conn->connect_errno){
                Daemon_Efuns::log_event("database","data_host=".$row["data_host"] ." connect_errno=".$new_conn->connect_error);
                return 0;
            }
        }
        
        $new_conn->query("SET NAMES UTF8");
        //保持连接,返回	                
	    $this->game_db = $new_conn;        
        return $this;
    }


    /**
     * 选择对应的游戏服 返回对应的信息
     * @param $server_id
     * @return int
     */
    public function select_server($server_id)
    {
        if ($this->server_list[$server_id]){
            return $this->server_list[$server_id];
        }
        return 0;
    }
    
    public function use_dbname($db_name)
    {
        $this->db_name = $db_name;
        $this->instance->query("use ".$this->db_name);
        return $this;
    }
	
	public function set_sql( $sql )                //自定义sql语句
	{
	    $this->sql = $sql;
	    return $this;
	}
	
	public function query_sql()
	{
	    return $this->sql;
	}
	
	//简单的addon构造方法
	public function addon( $where )
	{
	    $this->sql .= " ".$where;
	    return $this;
	}
	
	//查询
	public function query($sql = '', $server_id = 0)                //按照$sql进行查询  return $rows = Array();
	{
	    if (empty($this->instance)) $this->init();
	    
	    //多服模式
	    if ($server_id){
	        $this->init_game_db( $server_id );
	        $conn = $this->game_db;
	    }else{
	    //单服模式
	        $conn = $this->instance;
	    }
	    
	    if (empty($sql)) $sql = $this->sql;
	    if (empty($sql)) return -1;

        $rows = array();
	    if ($res = $conn->query($sql)){
	        if ($res->num_rows ){                  //数组为有内容
	            while( $row = $res->fetch_assoc()){
	                $rows[] = $row;
	            }
	        }
	    }else {
            Daemon_Efuns::log_event("database","sql=".$sql." error=".$conn->error  );
            return $res->error ;
	    }
	    return $rows;
	}
	
	
	//查询
	public function row_query($sql = '', $server_id = 0)                //按照$sql进行查询  return $rows = Array();
	{
        if (empty($this->instance)) $this->init();

        //多服模式
	    if ($server_id){
	        $this->init_game_db( $server_id );
	        $conn = $this->game_db;
	    }else{
	    //单服模式
	        $conn = $this->instance;
	    }

        if (empty($sql)) $sql = $this->sql;
        if (empty($sql)) return -1;

        $rows = array();
        if ($res = $conn->query($sql)){
            if ( $res->num_rows ){                  //数组为有内容
                while( $row = $res->fetch_row()){
                    $rows[] = $row;
                }
            }
        }else {
            Daemon_Efuns::log_event("database","sql=".$sql." error=".$conn->error  );
            return $conn->error ;
        }
        return $rows;
	}
	
	
	//执行update insert
    public function exec($sql = '', $server_id = 0)                 //按照$sql进行执行  return affected_rows or mysqlErrorCode(String)
    {
        if (empty($this->instance)) $this->init();
	    
	    //多服模式
	    if ($server_id){
	        $this->init_game_db( $server_id );
	        $conn = $this->game_db;
	    }else{
	    //单服模式
	        $conn = $this->instance;
	    }
	    
        if (empty($sql)) $sql = $this->sql;
	    if (empty($sql)) return -1;

	    if ($res = $conn->query($sql)){
	        $rows = $conn->affected_rows;      //影响的条数  >=0
	    }else {
            Daemon_Efuns::log_event("database","sql=".$sql." error=".$conn->error  );
            return $conn->error ;
	    }
	    return $rows;
    }


    /**
     * 返回最后insert_id
     * @param int $server_id
     * @return mixed
     */
    public function last_insert_id( $server_id = 0)           //执行数据插入时的ID
    {
        if (empty($this->instance)) $this->init();

        //多服模式
	    if ($server_id){
	        $this->init_game_db( $server_id );
	        $conn = $this->game_db;
	    }else{
	    //单服模式
	        $conn = $this->instance;
	    }

        return $conn->insert_id;
    }

    public function real_escape_string( $str, $server_id = 0)           //执行数据插入时的ID
    {
        if (empty($this->instance)) $this->init();

        //多服模式
        if ($server_id){
            $this->init_game_db( $server_id );
            $conn = $this->game_db;
        }else{
        //单服模式
            $conn = $this->instance;
        }

        return $conn->real_escape_string($str);
    }
    
    public function set_cols( $colValues)
    {
        
    }
    
    //$cols = array( $colName=>array($row1,$row2,$row3....), $colName2=>array($row1,$row2)...  )   多个数据
    //$cols = array( $colName=>$colValue ...  )   单一数据
    //将$cols = array(col_name=>col_value) 形成标准insert sql，插入数据库  
    public function insert( $table, $cols )            
    {
        $strKey = "(";
        $strValue = "(";
        
        $keys = array_keys($cols);
        $colsN = sizeof($keys);                     //字段数
        if (is_array($cols[$keys[0]])) {
            $rowsN = sizeof($cols[$keys[0]]);       //记录数
            //遍历所有记录，按字段形成一组数据
            for($i =0; $i < $rowsN; $i++){
                if ($i) $strValue .= ",(";
                for($j=0; $j < $colsN ; $j++){      //形成一行数据记录
                    if ($j) $strValue .= ",";
                    $strValue .= "'".addslashes($cols[$keys[$j]][$i])."'";
                }
                $strValue .= ")";
            }
            
            for($j=0; $j < $colsN ; $j++){      //形成一行的列记录
                if ($j) $strKey .= ",";
                $strKey .= addslashes($keys[$j]);      //形成col的数据
            }
            $strKey .= ")";
        }else{
            $rowsN = 0;                             //单一数据
            for($j=0; $j < $colsN ; $j++){
                if ($j) $strValue .= ",";
                if ($j) $strKey .= ",";
                $strValue .= "'".addslashes($cols[$keys[$j]])."'";
                $strKey .= addslashes($keys[$j]);           //形成col的数据
            }
            $strValue .= ")";
            $strKey .= ")";
        }
        
        $this->sql = "insert into ".addslashes($table)." ".$strKey." values ".$strValue;
        return $this;
    }
                                                
    //将$cols = array(col_name=>col_value) 形成标准update sql，update到数据库  
    public function update( $table, $cols, $escape = 1 )            
    {
        $str = '';
        $i = 0;
        foreach( $cols as $key=> $value){
            if ($i) $str .= ',';
            if ($escape)
                $str .= addslashes($key)." = '".addslashes($value)."'";
            else
                $str .= addslashes($key)." = ".addslashes($value);
            $i++;
        }
        
        $this->sql = 'update '.addslashes($table).' set '.$str;
        return $this;
    }
    
    //输入字段名数组， == select col1,col2,col3 from table 
    public function select( $table, $colsArray )       
    {
        $selectStr = addslashes(implode(",", $colsArray));
        $this->sql = 'select '.$selectStr.' from '.addslashes($table);
        return $this;
    }
    
     //删除记录
    public function delete( $table )
    {
        $this->sql = 'delete from '.addslashes($table);
        return $this;
    }
    
                                                        
    // $type默认为and 也可以为 or 
    // $compare 默认为= ，也可以是 '>', '<', '!=', 'like','is' 等其他合法运算符
    // 简单来说 解析器总是 $type.' '.$cols_key.$compare.$cols_value
    public function where($cols, $type = 'and', $compare = '=', $escape = 1)            //字段赋值 set_where可以被应用在 update/insert之前
    {
        //判断是否为合法运算符
        //if (!$this->_is_operator($compare)) return $this;    
        $str = '';
        if (is_array($cols) && sizeof($cols)){
            $i =0;
            foreach($cols as $key => $value){
                if ($i) $str .= ' '.$type.' ';
                if ($escape)
                    $str .= addslashes($key).' '.$compare." '".addslashes($value)."' ";
                else
                    $str .= addslashes($key).' '.$compare.' '.addslashes($value).' ';
                $i++;                        
            }
            $this->sql .= ' where '.$str;
        }
        return $this;
    }
    
    
    //where 的and模式
    public function and_where($cols, $compare = '=', $escape = 1)            //字段赋值 set_where可以被应用在 update/insert之前
    {
        //判断是否为合法运算符
        //if (!$this->_is_operator($compare)) return $this;    
        $type = 'and';
        $str = '';
        if (is_array($cols) && sizeof($cols)){
            $i =0;
            foreach($cols as $key => $value){
                if ($i) $str .= ' '.$type.' ';
                if ($escape)
                    $str .= addslashes($key).' '.$compare." '".addslashes($value)."' ";
                else
                    $str .= addslashes($key).' '.$compare.' '.addslashes($value).' ';
                $i++;                        
            }
            if (strstr($this->sql, 'where')){
                $this->sql .= $type.' ( '.$str.') ';
            }else {
                $this->sql .= ' where ('.$str.') ';
            }
        }
        return $this;
    }
    
    // where 的or模式
    public function or_where($cols, $compare = '=', $escape = 1)            //字段赋值 set_where可以被应用在 update/insert之前
    {
        //判断是否为合法运算符
        //if (!$this->_is_operator($compare)) return $this;    
        $type = 'or';
        $str = '';
        if (is_array($cols) && sizeof($cols)){
            $i =0;
            foreach($cols as $key => $value){
                if ($i) $str .= ' '.$type.' ';
                if ($escape)
                    $str .= addslashes($key).' '.$compare." '".addslashes($value)."' ";
                else
                    $str .= addslashes($key).' '.$compare.' '.addslashes($value).' ';
                $i++;                        
            }
            if (strstr($this->sql, 'where')){
                $this->sql .= $type.' ( '.$str.') ';
            }else {
                $this->sql .= ' where ('.$str.') ';
            }
        }
        return $this;
    }
    
    public function clear_sql()
    {
        $this->sql = '';
        return $this;
    }
 }
