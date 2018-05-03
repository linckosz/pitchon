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

	public function render(){
		ob_clean();
		Vanquish::setCookies();
		header("Content-type: application/json; charset=UTF-8");
		http_response_code(200); //Always use positive response (must go through success)
		echo json_encode($this->json, JSON_UNESCAPED_UNICODE);
		return exit(0);
	}
	
}
