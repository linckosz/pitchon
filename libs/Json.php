<?php

namespace libs;
use \libs\Vanquish;

class Json {

	protected $json = array(
		'extra' => array(),
		'error' => false,
		'status' => 200,
		'show' => false,
	);

	public function __construct($extra=array(), $error=false, $status=200, $show=false){
		if(!is_array($extra)){
			$extra = array('msg' => $extra);
		}
		$this->json['extra'] = $extra;
		$this->json['error'] = (bool) $error;
		$this->json['status'] = intval($status);
		$this->json['show'] = (bool) $show;
		return true;
	}

	public function render($status=200){
		ob_clean();
		Vanquish::setCookies();
		header("Content-type: application/json; charset=UTF-8");
		http_response_code($status); //For mos5 of the cases always use positive response 200 (must go through success). there is only one case you don't it it, but I forgt which one, it might be linked to file uploading.
		echo json_encode($this->json, JSON_UNESCAPED_UNICODE);
		return exit(0);
	}
	
}
