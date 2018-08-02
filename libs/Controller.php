<?php

namespace libs;
use \libs\Json;

abstract class Controller {

	public function __call($method, $args=array()){
		$app = \Slim\Slim::getInstance();
		$msg = $app->trans->getJSON('wrapper', 0, 4); //Sorry, we could not understand the request.
		if($app->bruno->jsonException){
			(new Json($msg, true, 404))->render();
		} else {
			echo $msg;
		}
		$app->stop();
		return false;
	}
}
