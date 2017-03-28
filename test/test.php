<?php
/*
 * Created on 2011-5-3
 *
 * WH
 */

 class Test_Test
 {

 	private function __construct() {}

 	public static function read_file($file)
 	{
		$han = fopen($file, "r");
		if($han) {
			while(!feof($han)) {
				$buf = trim(fgets($han, 4096));
				if(empty($buf)) continue;
				$arr[] = explode(" ", $buf);
			}
			fclose($han);
		}
		return $arr;
 	}

 	public static function main()
 	{
 		$arr = self::read_file("testCase.txt");

 		echo json_encode($arr);
 	}

 }

 Test_Test::main();
