<?php

// \time_checkpoint('ok');
function time_checkpoint($msg=''){
	global $app;
	if(isset($app->bruno) && $app->bruno->time_record && class_exists('\\libs\\Watch')){
		$milliseconds = round(microtime(true) * 1000);
		if(is_bool($app->bruno->time_record)){ //initialization
			$app->bruno->time_record = $milliseconds;
			$app->bruno->time_start = $milliseconds;
		}
		$diff = $milliseconds-$app->bruno->time_record;
		$detail = $diff . "ms to reach this point\n" . ($milliseconds-$app->bruno->time_start)  . "ms in total";
		\libs\Watch::php($detail , 'Checkpoint: '.$msg, __FILE__, __LINE__, false, false, true);
		$app->bruno->time_record = $milliseconds;
		return $diff;
	}
	return false;
}

function my_autoload($pClassName){
	$app = \Slim\Slim::getInstance();
	$pClassName = str_replace('\\', '/', $pClassName);
	if(file_exists($app->bruno->path.'/'.$pClassName.'.php')){
		include_once($app->bruno->path.'/'.$pClassName.'.php');
		//time_checkpoint($pClassName.'.php');
	}
}

// \micro_seconds();
function micro_seconds(){
	list($usec, $sec) = explode(' ', microtime());
	return (1000 * (int)$sec) + round(1000 * (float)$usec);
}

spl_autoload_register('my_autoload');
