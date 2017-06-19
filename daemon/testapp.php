<?php
//Daemon_testApp 
class Daemon_testApp
{
    private function __construct()
    {
    }

    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public static function test($args)
    {
	$num = $args["table"];

	$db = new Object_databased();
        $db->init();

	$sql = 'CREATE TABLE IF NOT EXISTS `mydb_'.$num.'` (`userId` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,KEY (`userId`))';
	$ret = $db->exec($sql);
	if ( is_string($ret) ){
            echo "false";
        }else{
            echo "true";
        }
	
	$args['table'] = $num+1;
	if($num < 10){
	    Daemon_Crontab::call_out("Daemon_testApp::test", 5, $args);
	}
    }
}
