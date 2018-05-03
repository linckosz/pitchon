<?php
//你好 Léo & Luka

namespace libs;

use \libs\Folders;

class Watch {

	//Cleaner help to clean the complete page at the very first call
	protected static $cleaner = false;

	//Special functions to see variables
	/*
	\libs\Watch::php(true, '$var', __FILE__, __LINE__, false, false, true);
	*/
	public static function php($var='yes', $comment='undefined', $filename=__FILE__, $fileline=__LINE__, $error=false, $reset=false, $cleaner=false){
		global $app;

		if($cleaner && !self::$cleaner){
			self::$cleaner = true;
			$reset = true;
		}

		if($error){
			$logPath = $app->bruno->logPath.'/php';
			$fic = $logPath.'/logPHP_'.date('ymd').'.txt';
		} else {
			$logPath = $app->bruno->logPath;
			$fic = $logPath.'/watchPHP_'.date('ymd').'.txt';
		}

		$folder = new Folders;
		$folder->createPath($logPath, 0770);

		if(file_exists($fic)){
			$truncate = 500000;
			if($reset){ $truncate = 0; }
			if(filesize($fic)>$truncate*2){ //Help to never get a file higher than 1MB, avoid to fulfill the server space in case of heavy bug
				if($fp = fopen($fic, 'r+')){ //We open the file in read/write, and place the cursor at the beginning
					@ftruncate($fp,$truncate); //Cut the file in half (like that it keep all first alerts)
					fclose($fp); //CLose the file
				}
			}
		}
		
		$dt = date("Y-m-d H:i:s (T)");

		$userid = null;
		if(isset($app->bruno->data['user_id'])){
			$userid = $app->bruno->data['user_id'];
		}
		
		if(is_array($var) || is_object($var)){
			$msg = (string)print_r($var,true);
		} else {
			$msg = (string)$var;
		}
		if($error){
			if(function_exists('\error\getTraceAsString')){
				$msg .= "\n".\error\getTraceAsString(new \Exception, 30);
			}
		} else {
			if(function_exists('\error\getTraceAsString')){
				//$msg .= "\n".\error\getTraceAsString(new \Exception, 30);
			}
		}

		$comment = (string)$comment;
		
		if(is_file($filename)){
			$path_parts = pathinfo($filename);
			$basename = $path_parts['basename'].' ('.$fileline.')';
		} else {
			$basename = 'undefined';
		}
		
		$msg = "
$comment =>
$basename | $dt | $userid
-------------------------------------
$msg
-------------------------------------

";

		error_log($msg, 3, $fic);
	}

	//Catch JS error message
	/*
	\libs\Watch::js();
	*/
	public static function js(){
		global $app;
		$logPath = $app->bruno->logPath.'/js';
		$dt = date("Y-m-d H:i:s (T)");
		
		$errmsg = json_decode($app->request->getBody());

		$errid = "unknown"; //User ID
		$erruser = "unknown"; //User Login
		if( isset($app->bruno) && isset($app->bruno->data) ){
			if(isset($app->bruno->data['user_id'])){
				$errid = $app->bruno->data['user_id'];
			}
			if(isset($app->bruno->data['user_email'])){
				$erruser = $app->bruno->data['user_email'];
			}
		}
		
		if(isset($_COOKIE) && isset($_COOKIE['ip'])){
			$errip = $_COOKIE['ip'];
		} else if(isset($_SERVER) && isset($_SERVER['REMOTE_ADDR'])){
			$errip = $_SERVER['REMOTE_ADDR'];
		} else {
			$errip = $app->request->getIp();
		}

		$url = $app->request->headers->Host.$app->request->getResourceUri();
		
		$err  = "DATE: $dt\n";
		$err .= "USER: $errid / $erruser / $errip\n";
		$err .= "URL : $url\n";
		$err .= "$errmsg\n\n\n";

$err = str_replace("\n","
",$err);

		$folder = new Folders;
		$folder->createPath($logPath, 0770);

		$fic = $logPath.'/logJS_'.date('ymd').'.txt';
		if(file_exists($fic)){
			if(filesize($fic)>1000000){ //Help to never get a file higher than 1MB, avoid to fulfill the server space in case of heavy bug
				if($fp = fopen($fic, 'r+')){ //We open the file in read/write, and place the cursor at the beginning
					@ftruncate($fp,500000); //Cut the file in half (like that it keep all first alerts)
					fclose($fp); //CLose the file
				}
			}
		}

		error_log($err, 3, $fic);
	}

}

//Uncomment to clean the Watch file
//\libs\Watch::php('-------------------------------------', 'Cleaner', __FILE__, false, true, false);
