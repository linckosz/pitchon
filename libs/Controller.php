<?php

namespace libs;

abstract class Controller {

	public function __call($method, $args=array()){
		$app = \Slim\Slim::getInstance();
		$msg = $app->trans->getJSON('wrapper', 0, 4); //Sorry, we could not understand the request.
		if($app->bruno->jsonException){
			$app->render(404, array('msg' => $msg,));
		} else {
			echo $msg;
		}
		$app->stop();
		return false;
	}
}
